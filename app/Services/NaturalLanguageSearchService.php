<?php

namespace App\Services;

use App\Models\Bed;
use App\Models\Complaint;
use App\Models\Hostel;
use App\Models\Invoice;
use App\Models\Student;
use Illuminate\Support\Facades\Http;

/**
 * Natural-language search for admin/warden. For safety, the AI is used ONLY
 * to classify the question into one of a fixed set of known intents — it
 * never generates or executes raw SQL. Each intent maps to a hand-written,
 * safe Eloquent query. This means a prompt-injected or malicious question
 * can, at worst, get misclassified — it can never read or modify data
 * outside these pre-approved queries.
 */
class NaturalLanguageSearchService
{
    private const INTENTS = [
        'unpaid_students',
        'overdue_invoices',
        'occupancy_summary',
        'complaints_by_hostel',
        'open_complaints',
    ];

    public function search(string $question): array
    {
        $intent = config('services.gemini.key')
            ? ($this->classifyWithGemini($question) ?? $this->classifyWithKeywords($question))
            : $this->classifyWithKeywords($question);

        return match ($intent) {
            'unpaid_students' => $this->unpaidStudents(),
            'overdue_invoices' => $this->overdueInvoices(),
            'occupancy_summary' => $this->occupancySummary(),
            'complaints_by_hostel' => $this->complaintsByHostel(),
            'open_complaints' => $this->openComplaints(),
            default => ['summary' => "I can answer questions about: unpaid students, overdue invoices, room occupancy, complaints by hostel, and open complaints. Try rephrasing your question.", 'columns' => [], 'rows' => []],
        };
    }

    private function classifyWithGemini(string $question): ?string
    {
        try {
            $intents = implode(', ', self::INTENTS);
            $prompt = "Classify this admin question into exactly one of these categories: {$intents}, or \"unknown\" if none fit.\n"
                . "Respond with ONLY the category word, nothing else.\n\nQuestion: {$question}";

            $response = Http::timeout(8)->post(
                'https://generativelanguage.googleapis.com/v1beta/models/' . config('services.gemini.model') . ':generateContent?key=' . config('services.gemini.key'),
                [
                    'contents' => [['parts' => [['text' => $prompt]]]],
                    'generationConfig' => ['temperature' => 0, 'maxOutputTokens' => 20],
                ]
            );

            if (! $response->successful()) {
                return null;
            }

            $intent = trim(strtolower($response->json('candidates.0.content.parts.0.text') ?? ''));

            return in_array($intent, self::INTENTS, true) ? $intent : null;
        } catch (\Throwable $e) {
            report($e);

            return null;
        }
    }

    private function classifyWithKeywords(string $question): string
    {
        $q = mb_strtolower($question);

        if (str_contains($q, 'overdue')) {
            return 'overdue_invoices';
        }
        if (str_contains($q, 'fee') || str_contains($q, 'due') || str_contains($q, 'বকেয়া') || str_contains($q, 'unpaid')) {
            return 'unpaid_students';
        }
        if (str_contains($q, 'occup') || str_contains($q, 'vacant') || str_contains($q, 'ফাঁকা') || str_contains($q, 'bed')) {
            return 'occupancy_summary';
        }
        if (str_contains($q, 'complaint') && (str_contains($q, 'hostel') || str_contains($q, 'block') || str_contains($q, 'ব্লক') || str_contains($q, 'most'))) {
            return 'complaints_by_hostel';
        }
        if (str_contains($q, 'complaint')) {
            return 'open_complaints';
        }

        return 'unknown';
    }

    private function unpaidStudents(): array
    {
        $rows = Student::query()
            ->withSum('invoices as total_amount', 'amount')
            ->withSum('invoices as total_paid', 'paid_amount')
            ->having('total_amount', '>', 0)
            ->get()
            ->map(function ($s) {
                $due = ($s->total_amount ?? 0) - ($s->total_paid ?? 0);

                return $due > 0 ? [$s->student_uid, $s->name, $s->phone ?? '-', '₹' . number_format($due, 2)] : null;
            })
            ->filter()
            ->sortByDesc(fn ($r) => (float) str_replace(['₹', ','], '', $r[3]))
            ->values();

        return [
            'summary' => $rows->count() . ' student(s) currently have outstanding dues.',
            'columns' => ['UID', 'Name', 'Phone', 'Due Amount'],
            'rows' => $rows->toArray(),
        ];
    }

    private function overdueInvoices(): array
    {
        $invoices = Invoice::with('student')->where('status', 'overdue')->orderByDesc('due_date')->get();

        return [
            'summary' => $invoices->count() . ' invoice(s) are overdue.',
            'columns' => ['Invoice No.', 'Student', 'Amount', 'Due Date'],
            'rows' => $invoices->map(fn ($i) => [
                $i->invoice_no, $i->student->name ?? '-', '₹' . number_format($i->amount, 2), $i->due_date->format('d M Y'),
            ])->toArray(),
        ];
    }

    private function occupancySummary(): array
    {
        $hostels = Hostel::with('blocks.floors.rooms.beds')->active()->get();

        $rows = $hostels->map(function ($hostel) {
            $beds = $hostel->blocks->flatMap->floors->flatMap->rooms->flatMap->beds;
            $total = $beds->count();
            $occupied = $beds->where('status', 'occupied')->count();
            $rate = $total > 0 ? round($occupied / $total * 100, 1) : 0;

            return [$hostel->name, $total, $occupied, $total - $occupied, "{$rate}%"];
        });

        return [
            'summary' => 'Occupancy summary across ' . $hostels->count() . ' hostel(s).',
            'columns' => ['Hostel', 'Total Beds', 'Occupied', 'Available', 'Occupancy Rate'],
            'rows' => $rows->toArray(),
        ];
    }

    private function complaintsByHostel(): array
    {
        $rows = Complaint::query()
            ->join('rooms', 'complaints.room_id', '=', 'rooms.id')
            ->join('floors', 'rooms.floor_id', '=', 'floors.id')
            ->join('blocks', 'floors.block_id', '=', 'blocks.id')
            ->join('hostels', 'blocks.hostel_id', '=', 'hostels.id')
            ->selectRaw('hostels.name as hostel_name, count(*) as total')
            ->groupBy('hostels.name')
            ->orderByDesc('total')
            ->get();

        return [
            'summary' => $rows->isNotEmpty() ? "\"{$rows->first()->hostel_name}\" has the most complaints ({$rows->first()->total})." : 'No complaints with a linked room found.',
            'columns' => ['Hostel', 'Complaint Count'],
            'rows' => $rows->map(fn ($r) => [$r->hostel_name, $r->total])->toArray(),
        ];
    }

    private function openComplaints(): array
    {
        $complaints = Complaint::with('student', 'category')->whereIn('status', ['open', 'in_progress'])->latest()->limit(50)->get();

        return [
            'summary' => $complaints->count() . ' complaint(s) are currently open or in progress.',
            'columns' => ['Ticket No.', 'Student', 'Category', 'Priority', 'Status'],
            'rows' => $complaints->map(fn ($c) => [
                $c->ticket_no, $c->student->name ?? '-', $c->category->name ?? 'Uncategorized', ucfirst($c->priority), ucfirst(str_replace('_', ' ', $c->status)),
            ])->toArray(),
        ];
    }
}

<?php

namespace App\Services;

use App\Models\MealMenu;
use App\Models\Notice;
use App\Models\Student;
use Illuminate\Support\Facades\Http;

/**
 * A student-facing 24/7 assistant. It never invents facts — it only answers
 * using the student's own live data (room, dues, today's menu, notices)
 * pulled fresh on every question, so it can never leak another student's
 * information and can never be "wrong" about numbers it wasn't given.
 *
 * With a Gemini API key configured it phrases the answer naturally in
 * whatever language the student asked in. Without a key it falls back to
 * simple keyword-matched, deterministic answers built from the same data —
 * so the chatbot always works, even with zero AI cost.
 */
class HostelChatbotService
{
    public function ask(Student $student, string $question): string
    {
        $context = $this->buildContext($student);

        if (config('services.gemini.key')) {
            $answer = $this->askGemini($question, $context);
            if ($answer !== null) {
                return $answer;
            }
        }

        return $this->answerWithKeywords($question, $context);
    }

    // Gathers only what THIS student is allowed to see — never a DB dump.
    private function buildContext(Student $student): array
    {
        $allocation = $student->currentAllocation;
        $hostelId = $allocation?->room?->floor?->block?->hostel_id;

        $unpaidInvoices = $student->invoices()->whereIn('status', ['unpaid', 'partial', 'overdue'])->get();
        $totalDue = $unpaidInvoices->sum('amount') - $unpaidInvoices->sum('paid_amount');

        $today = strtolower(now()->format('l'));
        $todayMenu = MealMenu::where(fn ($q) => $q->where('hostel_id', $hostelId)->orWhereNull('hostel_id'))
            ->where('day_of_week', $today)
            ->get()
            ->keyBy('meal_type');

        $notices = Notice::published()
            ->where(fn ($q) => $q->where('audience', 'all')->orWhere('audience', 'students'))
            ->latest('publish_date')
            ->limit(3)
            ->get(['title', 'body', 'publish_date']);

        return [
            'student_name' => $student->name,
            'room' => $allocation?->room?->room_number,
            'bed' => $allocation?->bed?->bed_number,
            'total_due' => round($totalDue, 2),
            'unpaid_invoice_count' => $unpaidInvoices->count(),
            'today_breakfast' => $todayMenu['breakfast']->items ?? 'Not set',
            'today_lunch' => $todayMenu['lunch']->items ?? 'Not set',
            'today_dinner' => $todayMenu['dinner']->items ?? 'Not set',
            'notices' => $notices->map(fn ($n) => "{$n->title}: " . \Illuminate\Support\Str::limit($n->body, 100))->toArray(),
        ];
    }

    private function askGemini(string $question, array $context): ?string
    {
        try {
            $model = config('services.gemini.model');
            $apiKey = config('services.gemini.key');

            $contextText = "Student name: {$context['student_name']}\n"
                . 'Room: ' . ($context['room'] ? "Room {$context['room']}, Bed {$context['bed']}" : 'Not allocated yet') . "\n"
                . "Total due: ৳{$context['total_due']} across {$context['unpaid_invoice_count']} invoice(s)\n"
                . "Today's breakfast: {$context['today_breakfast']}\n"
                . "Today's lunch: {$context['today_lunch']}\n"
                . "Today's dinner: {$context['today_dinner']}\n"
                . "Recent notices:\n- " . implode("\n- ", $context['notices']);

            $prompt = "You are a helpful hostel assistant chatbot for a student. Answer ONLY using the data below — never invent room numbers, amounts, or menu items. "
                . "If the question can't be answered from this data, politely tell the student to contact the hostel office. Keep answers short (2-3 sentences), and reply in the same language the student used.\n\n"
                . "STUDENT DATA:\n{$contextText}\n\nSTUDENT QUESTION: {$question}";

            $response = Http::timeout(10)
                ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}", [
                    'contents' => [['parts' => [['text' => $prompt]]]],
                    'generationConfig' => ['temperature' => 0.3, 'maxOutputTokens' => 1000],
                ]);

            if (! $response->successful()) {
                return null;
            }

            $text = trim($response->json('candidates.0.content.parts.0.text') ?? '');

            return $text !== '' ? $text : null;
        } catch (\Throwable $e) {
            report($e);

            return null;
        }
    }

    private function answerWithKeywords(string $question, array $context): string
    {
        $q = mb_strtolower($question);

        // Room / bed
        if (str_contains($q, 'room') || str_contains($q, 'বেড') || str_contains($q, 'রুম') || str_contains($q, 'bed')) {
            return $context['room']
                ? "You're currently allocated to Room {$context['room']}, Bed {$context['bed']}."
                : "You don't have a room allocated yet. Please contact the hostel office.";
        }

        // Fees / dues
        if (str_contains($q, 'fee') || str_contains($q, 'due') || str_contains($q, 'পেমেন্ট') || str_contains($q, 'বকেয়া') || str_contains($q, 'টাকা') || str_contains($q, 'invoice')) {
            return $context['total_due'] > 0
                ? "You have ৳{$context['total_due']} due across {$context['unpaid_invoice_count']} invoice(s). Check 'My Invoices' for details."
                : "You have no outstanding dues right now. 🎉";
        }

        // Mess / menu / food
        if (str_contains($q, 'mess') || str_contains($q, 'menu') || str_contains($q, 'খাবার') || str_contains($q, 'food') || str_contains($q, 'meal')) {
            return "Today's menu — Breakfast: {$context['today_breakfast']}; Lunch: {$context['today_lunch']}; Dinner: {$context['today_dinner']}.";
        }

        // Notices
        if (str_contains($q, 'notice') || str_contains($q, 'নোটিশ') || str_contains($q, 'announcement')) {
            return count($context['notices']) > 0
                ? "Latest notices:\n- " . implode("\n- ", $context['notices'])
                : 'There are no recent notices.';
        }

        return "I can help with: room info, fees/dues, today's mess menu, and notices. "
            . "Try asking something like \"What's my room?\", \"Do I have any dues?\", or \"What's for lunch today?\". "
            . 'For anything else, please contact the hostel office.';
    }
}
<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\AssetDamageReport;
use App\Models\Bed;
use App\Models\Complaint;
use App\Models\Hostel;
use App\Models\Invoice;
use App\Models\Student;
use App\Models\Visitor;
use Illuminate\Support\Facades\Http;

class NaturalLanguageSearchService
{
    private const INTENTS = [
        'all_students',
        'active_students',
        'unpaid_students',
        'allocated_students',
        'unallocated_students',
        'students_by_hostel',

        'occupancy_summary',
        'vacant_rooms',
        'full_rooms',
        'available_beds',
        'highest_occupancy_hostel',
        'active_room_allocations',

        'overdue_invoices',
        'unpaid_invoices',
        'recent_payments',
        'monthly_revenue',
        'highest_outstanding_students',

        'open_complaints',
        'complaints_by_hostel',
        'high_priority_complaints',
        'recently_resolved_complaints',

        'todays_visitors',
        'pending_visitors',
        'todays_attendance',
        'absent_students',

        'low_stock_assets',
        'damaged_assets',
        'room_assets',

        'monthly_mess_cuts',
        'monthly_mess_bills',
    ];

    public function search(string $question): array
    {
        $intent = config('services.gemini.key')
            ? ($this->classifyWithGemini($question)
                ?? $this->classifyWithKeywords($question))
            : $this->classifyWithKeywords($question);

        return match ($intent) {

            // Students
            'all_students' => $this->allStudents(),
            'active_students' => $this->activeStudents(),
            'unpaid_students' => $this->unpaidStudents(),
            'allocated_students' => $this->allocatedStudents(),
            'unallocated_students' => $this->unallocatedStudents(),
            'students_by_hostel' => $this->studentsByHostel(),

            // Rooms / Beds
            'occupancy_summary' => $this->occupancySummary(),
            'vacant_rooms' => $this->vacantRooms(),
            'full_rooms' => $this->fullRooms(),
            'available_beds' => $this->availableBeds(),
            'highest_occupancy_hostel' => $this->highestOccupancyHostel(),
            'active_room_allocations' => $this->activeRoomAllocations(),

            // Finance
            'overdue_invoices' => $this->overdueInvoices(),
            'unpaid_invoices' => $this->unpaidInvoices(),
            'recent_payments' => $this->recentPayments(),
            'monthly_revenue' => $this->monthlyRevenue(),
            'highest_outstanding_students' => $this->highestOutstandingStudents(),

            // Complaints
            'open_complaints' => $this->openComplaints(),
            'complaints_by_hostel' => $this->complaintsByHostel(),
            'high_priority_complaints' => $this->highPriorityComplaints(),
            'recently_resolved_complaints' => $this->recentlyResolvedComplaints(),

            // Visitors / Attendance
            'todays_visitors' => $this->todaysVisitors(),
            'pending_visitors' => $this->pendingVisitors(),
            'todays_attendance' => $this->todaysAttendance(),
            'absent_students' => $this->absentStudents(),

            // Assets
            'low_stock_assets' => $this->lowStockAssets(),
            'damaged_assets' => $this->damagedAssets(),
            'room_assets' => $this->roomAssets(),

            // Mess
            'monthly_mess_cuts' => $this->monthlyMessCuts(),
            'monthly_mess_bills' => $this->monthlyMessBills(),

            default => [
                'summary' => 'I can help with students, rooms, beds, fees, payments, complaints, visitors, attendance, assets and mess management. Try a more specific question.',
                'columns' => [],
                'rows' => [],
            ],
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Intent Classification
    |--------------------------------------------------------------------------
    */

    private function classifyWithGemini(string $question): ?string
    {
        try {
            $intents = implode(', ', self::INTENTS);

            $prompt = <<<PROMPT
Classify the admin question into exactly ONE of these intents:

{$intents}

Return ONLY the intent name.

Question:
{$question}
PROMPT;

            $response = Http::timeout(8)->post(
                'https://generativelanguage.googleapis.com/v1beta/models/'
                . config('services.gemini.model')
                . ':generateContent?key='
                . config('services.gemini.key'),
                [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => 0,
                        'maxOutputTokens' => 30,
                    ],
                ]
            );

            if (! $response->successful()) {
                return null;
            }

            $intent = trim(
                strtolower(
                    $response->json('candidates.0.content.parts.0.text') ?? ''
                )
            );

            return in_array($intent, self::INTENTS, true)
                ? $intent
                : null;

        } catch (\Throwable $e) {
            report($e);

            return null;
        }
    }

    private function classifyWithKeywords(string $question): string
    {
        $q = mb_strtolower(trim($question));

        /*
        |--------------------------------------------------------------------------
        | Students
        |--------------------------------------------------------------------------
        */

        if (
            str_contains($q, 'all students') ||
            str_contains($q, 'show students') ||
            str_contains($q, 'student list')
        ) {
            return 'all_students';
        }

        if (
            str_contains($q, 'active students') ||
            str_contains($q, 'currently active students')
        ) {
            return 'active_students';
        }

        if (
            str_contains($q, 'unpaid student') ||
            str_contains($q, 'unpaid students') ||
            str_contains($q, 'outstanding student') ||
            str_contains($q, 'outstanding students') ||
            str_contains($q, 'pending fees') ||
            str_contains($q, 'has not paid')
        ) {
            return 'unpaid_students';
        }

        if (
            str_contains($q, 'allocated students') ||
            str_contains($q, 'students allocated') ||
            str_contains($q, 'students with room')
        ) {
            return 'allocated_students';
        }

        if (
            str_contains($q, 'unallocated students') ||
            str_contains($q, 'students without room') ||
            str_contains($q, 'students not allocated')
        ) {
            return 'unallocated_students';
        }

        if (
            str_contains($q, 'students by hostel') ||
            str_contains($q, 'hostel wise students') ||
            str_contains($q, 'student by hostel')
        ) {
            return 'students_by_hostel';
        }

        /*
        |--------------------------------------------------------------------------
        | Rooms / Beds
        |--------------------------------------------------------------------------
        */

        if (
            str_contains($q, 'occupancy') ||
            str_contains($q, 'room occupancy') ||
            str_contains($q, 'occupied beds')
        ) {
            return 'occupancy_summary';
        }

        if (
            str_contains($q, 'vacant room') ||
            str_contains($q, 'empty room') ||
            str_contains($q, 'empty rooms')
        ) {
            return 'vacant_rooms';
        }

        if (
            str_contains($q, 'full room') ||
            str_contains($q, 'fully occupied room')
        ) {
            return 'full_rooms';
        }

        if (
            str_contains($q, 'available beds') ||
            str_contains($q, 'vacant beds') ||
            str_contains($q, 'free beds')
        ) {
            return 'available_beds';
        }

        if (
            str_contains($q, 'highest occupancy') ||
            str_contains($q, 'most occupied hostel')
        ) {
            return 'highest_occupancy_hostel';
        }

        if (
            str_contains($q, 'active room allocation') ||
            str_contains($q, 'current room allocation') ||
            str_contains($q, 'current allocations')
        ) {
            return 'active_room_allocations';
        }

        /*
        |--------------------------------------------------------------------------
        | Finance
        |--------------------------------------------------------------------------
        */

        if (
            str_contains($q, 'overdue invoice') ||
            str_contains($q, 'overdue invoices')
        ) {
            return 'overdue_invoices';
        }

        if (
            str_contains($q, 'unpaid invoice') ||
            str_contains($q, 'unpaid invoices') ||
            str_contains($q, 'pending invoice')
        ) {
            return 'unpaid_invoices';
        }

        if (
            str_contains($q, 'recent payment') ||
            str_contains($q, 'recent payments') ||
            str_contains($q, 'latest payments')
        ) {
            return 'recent_payments';
        }

        if (
            str_contains($q, 'monthly revenue') ||
            str_contains($q, 'this month revenue') ||
            str_contains($q, 'revenue this month') ||
            str_contains($q, 'monthly collection')
        ) {
            return 'monthly_revenue';
        }

        if (
            str_contains($q, 'highest outstanding') ||
            str_contains($q, 'students owe most') ||
            str_contains($q, 'highest dues')
        ) {
            return 'highest_outstanding_students';
        }

        /*
        |--------------------------------------------------------------------------
        | Complaints
        |--------------------------------------------------------------------------
        */

        if (
            str_contains($q, 'open complaint') ||
            str_contains($q, 'open complaints') ||
            str_contains($q, 'pending complaints')
        ) {
            return 'open_complaints';
        }

        if (
            str_contains($q, 'complaints by hostel') ||
            str_contains($q, 'hostel wise complaints') ||
            str_contains($q, 'complaints hostel')
        ) {
            return 'complaints_by_hostel';
        }

        if (
            str_contains($q, 'high priority complaint') ||
            str_contains($q, 'urgent complaint') ||
            str_contains($q, 'high priority complaints')
        ) {
            return 'high_priority_complaints';
        }

        if (
            str_contains($q, 'resolved complaints') ||
            str_contains($q, 'recently resolved')
        ) {
            return 'recently_resolved_complaints';
        }

        /*
        |--------------------------------------------------------------------------
        | Visitors
        |--------------------------------------------------------------------------
        */

        if (
            str_contains($q, 'today visitors') ||
            str_contains($q, "today's visitors") ||
            str_contains($q, 'visitors today')
        ) {
            return 'todays_visitors';
        }

        if (
            str_contains($q, 'pending visitors') ||
            str_contains($q, 'pending visitor')
        ) {
            return 'pending_visitors';
        }

        /*
        |--------------------------------------------------------------------------
        | Attendance
        |--------------------------------------------------------------------------
        */

        if (
            str_contains($q, 'today attendance') ||
            str_contains($q, "today's attendance") ||
            str_contains($q, 'attendance today') ||
            str_contains($q, 'present today')
        ) {
            return 'todays_attendance';
        }

        if (
            str_contains($q, 'absent students') ||
            str_contains($q, 'students absent') ||
            str_contains($q, 'who is absent')
        ) {
            return 'absent_students';
        }

        /*
        |--------------------------------------------------------------------------
        | Assets
        |--------------------------------------------------------------------------
        */

        if (
            str_contains($q, 'low stock') ||
            str_contains($q, 'low stock assets')
        ) {
            return 'low_stock_assets';
        }

        if (
            str_contains($q, 'damaged asset') ||
            str_contains($q, 'damaged assets') ||
            str_contains($q, 'asset damage')
        ) {
            return 'damaged_assets';
        }

        if (
            str_contains($q, 'assets assigned to rooms') ||
            str_contains($q, 'room assets') ||
            str_contains($q, 'assigned assets')
        ) {
            return 'room_assets';
        }

        /*
        |--------------------------------------------------------------------------
        | Mess
        |--------------------------------------------------------------------------
        */

        if (
            str_contains($q, 'mess cuts') ||
            str_contains($q, 'mess cut this month') ||
            str_contains($q, 'monthly mess cuts')
        ) {
            return 'monthly_mess_cuts';
        }

        if (
            str_contains($q, 'mess bills') ||
            str_contains($q, 'mess bill this month') ||
            str_contains($q, 'monthly mess bills')
        ) {
            return 'monthly_mess_bills';
        }

        return 'unknown';
    }

    /*
    |--------------------------------------------------------------------------
    | Student Queries
    |--------------------------------------------------------------------------
    */

    private function allStudents(): array
    {
        $students = Student::latest()->limit(100)->get();

        return [
            'summary' => $students->count() . ' student(s) found.',
            'columns' => ['UID', 'Name', 'Phone', 'Status'],
            'rows' => $students->map(fn ($s) => [
                $s->student_uid,
                $s->name,
                $s->phone ?? '-',
                ucfirst($s->status ?? '-'),
            ])->toArray(),
        ];
    }

    private function activeStudents(): array
    {
        $students = Student::where('status', 'active')
            ->latest()
            ->get();

        return [
            'summary' => $students->count() . ' active student(s).',
            'columns' => ['UID', 'Name', 'Phone'],
            'rows' => $students->map(fn ($s) => [
                $s->student_uid,
                $s->name,
                $s->phone ?? '-',
            ])->toArray(),
        ];
    }

    private function unpaidStudents(): array
    {
        $rows = Student::query()
            ->withSum('invoices as total_amount', 'amount')
            ->withSum('invoices as total_paid', 'paid_amount')
            ->having('total_amount', '>', 0)
            ->get()
            ->map(function ($s) {

                $due = ($s->total_amount ?? 0)
                    - ($s->total_paid ?? 0);

                return $due > 0
                    ? [
                        $s->student_uid,
                        $s->name,
                        $s->phone ?? '-',
                        '₹' . number_format($due, 2),
                    ]
                    : null;
            })
            ->filter()
            ->sortByDesc(
                fn ($r) =>
                    (float) str_replace(['₹', ','], '', $r[3])
            )
            ->values();

        return [
            'summary' => $rows->count()
                . ' student(s) currently have outstanding dues.',
            'columns' => ['UID', 'Name', 'Phone', 'Due Amount'],
            'rows' => $rows->toArray(),
        ];
    }

    private function allocatedStudents(): array
    {
        $students = Student::whereHas('roomAllocations', function ($q) {
            $q->where('status', 'active');
        })->get();

        return [
            'summary' => $students->count()
                . ' student(s) currently have room allocations.',
            'columns' => ['UID', 'Name', 'Phone'],
            'rows' => $students->map(fn ($s) => [
                $s->student_uid,
                $s->name,
                $s->phone ?? '-',
            ])->toArray(),
        ];
    }

    private function unallocatedStudents(): array
    {
        $students = Student::whereDoesntHave('roomAllocations', function ($q) {
            $q->where('status', 'active');
        })->get();

        return [
            'summary' => $students->count()
                . ' student(s) have no active room allocation.',
            'columns' => ['UID', 'Name', 'Phone'],
            'rows' => $students->map(fn ($s) => [
                $s->student_uid,
                $s->name,
                $s->phone ?? '-',
            ])->toArray(),
        ];
    }

    private function studentsByHostel(): array
    {
        $hostels = Hostel::withCount([
            'blocks as student_count' => function ($query) {
                $query->join('floors', 'blocks.id', '=', 'floors.block_id')
                    ->join('rooms', 'floors.id', '=', 'rooms.floor_id')
                    ->join('room_allocations', 'rooms.id', '=', 'room_allocations.room_id')
                    ->where('room_allocations.status', 'active');
            }
        ])->get();

        return [
            'summary' => 'Student count by hostel.',
            'columns' => ['Hostel', 'Active Students'],
            'rows' => $hostels->map(fn ($h) => [
                $h->name,
                $h->student_count,
            ])->toArray(),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Occupancy
    |--------------------------------------------------------------------------
    */

    private function occupancySummary(): array
    {
        $hostels = Hostel::with('blocks.floors.rooms.beds')
            ->active()
            ->get();

        $rows = $hostels->map(function ($hostel) {

            $beds = $hostel->blocks
                ->flatMap->floors
                ->flatMap->rooms
                ->flatMap->beds;

            $total = $beds->count();

            $occupied = $beds
                ->where('status', 'occupied')
                ->count();

            $rate = $total > 0
                ? round($occupied / $total * 100, 1)
                : 0;

            return [
                $hostel->name,
                $total,
                $occupied,
                $total - $occupied,
                "{$rate}%",
            ];
        });

        return [
            'summary' => 'Occupancy summary across '
                . $hostels->count()
                . ' hostel(s).',
            'columns' => [
                'Hostel',
                'Total Beds',
                'Occupied',
                'Available',
                'Occupancy Rate'
            ],
            'rows' => $rows->toArray(),
        ];
    }

    private function vacantRooms(): array
    {
        $rooms = \App\Models\Room::with('beds')
            ->get()
            ->filter(function ($room) {
                return $room->beds->where('status', 'occupied')->count() === 0;
            });

        return [
            'summary' => $rooms->count() . ' vacant room(s).',
            'columns' => ['Room Number', 'Total Beds'],
            'rows' => $rooms->map(fn ($r) => [
                $r->room_number,
                $r->beds->count(),
            ])->values()->toArray(),
        ];
    }

    private function fullRooms(): array
    {
        $rooms = \App\Models\Room::with('beds')
            ->get()
            ->filter(function ($room) {
                $total = $room->beds->count();

                return $total > 0 &&
                    $room->beds->where('status', 'occupied')->count() >= $total;
            });

        return [
            'summary' => $rooms->count() . ' fully occupied room(s).',
            'columns' => ['Room Number', 'Beds'],
            'rows' => $rooms->map(fn ($r) => [
                $r->room_number,
                $r->beds->count(),
            ])->values()->toArray(),
        ];
    }

    private function availableBeds(): array
    {
        $beds = Bed::where('status', '!=', 'occupied')->get();

        return [
            'summary' => $beds->count() . ' available bed(s).',
            'columns' => ['Bed ID', 'Status'],
            'rows' => $beds->map(fn ($b) => [
                $b->id,
                ucfirst($b->status),
            ])->toArray(),
        ];
    }

    private function highestOccupancyHostel(): array
    {
        $hostels = Hostel::with('blocks.floors.rooms.beds')
            ->active()
            ->get();

        $rows = $hostels->map(function ($hostel) {

            $beds = $hostel->blocks
                ->flatMap->floors
                ->flatMap->rooms
                ->flatMap->beds;

            $total = $beds->count();

            $occupied = $beds
                ->where('status', 'occupied')
                ->count();

            $rate = $total > 0
                ? round($occupied / $total * 100, 1)
                : 0;

            return [
                $hostel->name,
                $total,
                $occupied,
                "{$rate}%",
            ];
        })->sortByDesc(fn ($row) => (float) str_replace('%', '', $row[3]))
            ->values();

        return [
            'summary' => $rows->isNotEmpty()
                ? $rows->first()[0] . ' has the highest occupancy.'
                : 'No hostel data found.',
            'columns' => [
                'Hostel',
                'Total Beds',
                'Occupied',
                'Occupancy Rate'
            ],
            'rows' => $rows->toArray(),
        ];
    }

    private function activeRoomAllocations(): array
    {
        $allocations = \App\Models\RoomAllocation::with([
            'student',
            'room',
            'bed'
        ])
            ->where('status', 'active')
            ->latest()
            ->limit(100)
            ->get();

        return [
            'summary' => $allocations->count()
                . ' active room allocation(s).',
            'columns' => [
                'Student',
                'Room',
                'Bed',
                'Allocated Date'
            ],
            'rows' => $allocations->map(fn ($a) => [
                $a->student->name ?? '-',
                $a->room->room_number ?? '-',
                $a->bed->id ?? '-',
                optional($a->allocated_date)->format('d M Y') ?? '-',
            ])->toArray(),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Finance
    |--------------------------------------------------------------------------
    */

    private function overdueInvoices(): array
    {
        $invoices = Invoice::with('student')
            ->where('status', 'overdue')
            ->orderByDesc('due_date')
            ->get();

        return [
            'summary' => $invoices->count()
                . ' invoice(s) are overdue.',
            'columns' => [
                'Invoice No.',
                'Student',
                'Amount',
                'Due Date'
            ],
            'rows' => $invoices->map(fn ($i) => [
                $i->invoice_no,
                $i->student->name ?? '-',
                '₹' . number_format($i->amount, 2),
                optional($i->due_date)->format('d M Y') ?? '-',
            ])->toArray(),
        ];
    }

    private function unpaidInvoices(): array
    {
        $invoices = Invoice::with('student')
            ->whereIn('status', ['unpaid', 'pending', 'overdue'])
            ->latest()
            ->get();

        return [
            'summary' => $invoices->count()
                . ' unpaid invoice(s).',
            'columns' => [
                'Invoice No.',
                'Student',
                'Amount',
                'Status'
            ],
            'rows' => $invoices->map(fn ($i) => [
                $i->invoice_no,
                $i->student->name ?? '-',
                '₹' . number_format($i->amount, 2),
                ucfirst($i->status),
            ])->toArray(),
        ];
    }

    private function recentPayments(): array
    {
        $payments = \App\Models\Payment::with('student')
            ->latest()
            ->limit(50)
            ->get();

        return [
            'summary' => $payments->count()
                . ' recent payment(s).',
            'columns' => [
                'Student',
                'Amount',
                'Payment Date',
                'Method'
            ],
            'rows' => $payments->map(fn ($p) => [
                $p->student->name ?? '-',
                '₹' . number_format($p->amount, 2),
                optional($p->payment_date)->format('d M Y') ?? '-',
                $p->payment_method ?? '-',
            ])->toArray(),
        ];
    }

    private function monthlyRevenue(): array
    {
        $start = now()->startOfMonth();
        $end = now()->endOfMonth();

        $payments = \App\Models\Payment::whereBetween(
            'payment_date',
            [$start, $end]
        )->get();

        $total = $payments->sum('amount');

        return [
            'summary' => 'Revenue collected this month: ₹'
                . number_format($total, 2),
            'columns' => ['Month', 'Total Collection'],
            'rows' => [[
                now()->format('F Y'),
                '₹' . number_format($total, 2),
            ]],
        ];
    }

    private function highestOutstandingStudents(): array
    {
        $rows = Student::withSum(
            'invoices as total_amount',
            'amount'
        )
            ->withSum(
                'invoices as total_paid',
                'paid_amount'
            )
            ->get()
            ->map(function ($s) {

                $due = ($s->total_amount ?? 0)
                    - ($s->total_paid ?? 0);

                return [
                    $s->student_uid,
                    $s->name,
                    $due,
                ];
            })
            ->filter(fn ($r) => $r[2] > 0)
            ->sortByDesc(fn ($r) => $r[2])
            ->take(20)
            ->map(fn ($r) => [
                $r[0],
                $r[1],
                '₹' . number_format($r[2], 2),
            ])
            ->values();

        return [
            'summary' => 'Students with highest outstanding dues.',
            'columns' => ['UID', 'Name', 'Outstanding'],
            'rows' => $rows->toArray(),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Complaints
    |--------------------------------------------------------------------------
    */

    private function openComplaints(): array
    {
        $complaints = Complaint::with('student', 'category')
            ->whereIn('status', ['open', 'in_progress'])
            ->latest()
            ->limit(50)
            ->get();

        return [
            'summary' => $complaints->count()
                . ' complaint(s) are currently open or in progress.',
            'columns' => [
                'Ticket No.',
                'Student',
                'Category',
                'Priority',
                'Status'
            ],
            'rows' => $complaints->map(fn ($c) => [
                $c->ticket_no,
                $c->student->name ?? '-',
                $c->category->name ?? 'Uncategorized',
                ucfirst($c->priority),
                ucfirst(str_replace('_', ' ', $c->status)),
            ])->toArray(),
        ];
    }

    private function complaintsByHostel(): array
    {
        $rows = Complaint::query()
            ->join('rooms', 'complaints.room_id', '=', 'rooms.id')
            ->join('floors', 'rooms.floor_id', '=', 'floors.id')
            ->join('blocks', 'floors.block_id', '=', 'blocks.id')
            ->join('hostels', 'blocks.hostel_id', '=', 'hostels.id')
            ->selectRaw(
                'hostels.name as hostel_name, count(*) as total'
            )
            ->groupBy('hostels.name')
            ->orderByDesc('total')
            ->get();

        return [
            'summary' => $rows->isNotEmpty()
                ? "{$rows->first()->hostel_name} has the most complaints ({$rows->first()->total})."
                : 'No complaints with a linked room found.',
            'columns' => ['Hostel', 'Complaint Count'],
            'rows' => $rows->map(fn ($r) => [
                $r->hostel_name,
                $r->total
            ])->toArray(),
        ];
    }

    private function highPriorityComplaints(): array
    {
        $complaints = Complaint::with('student', 'category')
            ->whereIn('priority', ['high', 'urgent'])
            ->whereIn('status', ['open', 'in_progress'])
            ->latest()
            ->get();

        return [
            'summary' => $complaints->count()
                . ' high priority complaint(s).',
            'columns' => [
                'Ticket',
                'Student',
                'Category',
                'Priority',
                'Status'
            ],
            'rows' => $complaints->map(fn ($c) => [
                $c->ticket_no,
                $c->student->name ?? '-',
                $c->category->name ?? '-',
                ucfirst($c->priority),
                ucfirst(str_replace('_', ' ', $c->status)),
            ])->toArray(),
        ];
    }

    private function recentlyResolvedComplaints(): array
    {
        $complaints = Complaint::with('student', 'category')
            ->whereIn('status', ['resolved', 'closed'])
            ->latest('updated_at')
            ->limit(50)
            ->get();

        return [
            'summary' => $complaints->count()
                . ' recently resolved/closed complaint(s).',
            'columns' => [
                'Ticket',
                'Student',
                'Category',
                'Status',
                'Updated'
            ],
            'rows' => $complaints->map(fn ($c) => [
                $c->ticket_no,
                $c->student->name ?? '-',
                $c->category->name ?? '-',
                ucfirst($c->status),
                $c->updated_at->format('d M Y'),
            ])->toArray(),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Visitors / Attendance
    |--------------------------------------------------------------------------
    */

    private function todaysVisitors(): array
    {
        $visitors = Visitor::whereDate(
            'created_at',
            today()
        )->latest()->limit(50)->get();

        return [
            'summary' => $visitors->count()
                . ' visitor(s) recorded today.',
            'columns' => [
                'Visitor',
                'Phone',
                'Status'
            ],
            'rows' => $visitors->map(fn ($v) => [
                $v->name ?? $v->visitor_name ?? '-',
                $v->phone ?? '-',
                ucfirst($v->status ?? '-'),
            ])->toArray(),
        ];
    }

    private function pendingVisitors(): array
    {
        $visitors = Visitor::whereIn(
            'status',
            ['pending', 'expected']
        )->latest()->limit(50)->get();

        return [
            'summary' => $visitors->count()
                . ' pending visitor(s).',
            'columns' => [
                'Visitor',
                'Phone',
                'Status'
            ],
            'rows' => $visitors->map(fn ($v) => [
                $v->name ?? $v->visitor_name ?? '-',
                $v->phone ?? '-',
                ucfirst($v->status ?? '-'),
            ])->toArray(),
        ];
    }

    private function todaysAttendance(): array
    {
        $attendance = \App\Models\Attendance::whereDate(
            'date',
            today()
        )->get();

        return [
            'summary' => $attendance->count()
                . ' attendance record(s) today.',
            'columns' => ['Status', 'Count'],
            'rows' => $attendance
                ->groupBy('status')
                ->map(fn ($items, $status) => [
                    ucfirst($status),
                    $items->count()
                ])
                ->values()
                ->toArray(),
        ];
    }

    private function absentStudents(): array
    {
        $attendance = \App\Models\Attendance::with('student')
            ->whereDate('date', today())
            ->where('status', 'absent')
            ->get();

        return [
            'summary' => $attendance->count()
                . ' student(s) absent today.',
            'columns' => ['UID', 'Student'],
            'rows' => $attendance->map(fn ($a) => [
                $a->student->student_uid ?? '-',
                $a->student->name ?? '-',
            ])->toArray(),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Assets
    |--------------------------------------------------------------------------
    */

    private function lowStockAssets(): array
    {
        $assets = Asset::where(
            'low_stock_threshold',
            '>',
            0
        )
            ->whereColumn(
                'quantity_available',
                '<=',
                'low_stock_threshold'
            )
            ->latest()
            ->get();

        return [
            'summary' => $assets->count()
                . ' low stock asset(s).',
            'columns' => [
                'Asset',
                'Available',
                'Threshold'
            ],
            'rows' => $assets->map(fn ($a) => [
                $a->name,
                $a->quantity_available,
                $a->low_stock_threshold,
            ])->toArray(),
        ];
    }

    private function damagedAssets(): array
    {
        $reports = AssetDamageReport::with('asset', 'room')
            ->whereIn('status', [
                'reported',
                'under_repair'
            ])
            ->latest()
            ->get();

        return [
            'summary' => $reports->count()
                . ' damaged asset report(s).',
            'columns' => [
                'Asset',
                'Room',
                'Status'
            ],
            'rows' => $reports->map(fn ($r) => [
                $r->asset->name ?? '-',
                $r->room->room_number ?? '-',
                ucfirst(str_replace('_', ' ', $r->status)),
            ])->toArray(),
        ];
    }

    private function roomAssets(): array
    {
        $assignments = \App\Models\AssetRoomAssignment::with([
            'asset',
            'room'
        ])->latest()->limit(100)->get();

        return [
            'summary' => $assignments->count()
                . ' asset-room assignment(s).',
            'columns' => [
                'Asset',
                'Room',
                'Quantity'
            ],
            'rows' => $assignments->map(fn ($a) => [
                $a->asset->name ?? '-',
                $a->room->room_number ?? '-',
                $a->quantity,
            ])->toArray(),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Mess
    |--------------------------------------------------------------------------
    */

    private function monthlyMessCuts(): array
    {
        $model = \App\Models\MessCut::query();

        if (\Illuminate\Support\Facades\Schema::hasColumn(
            $model->getModel()->getTable(),
            'cut_date'
        )) {
            $model->whereBetween(
                'cut_date',
                [
                    now()->startOfMonth()->toDateString(),
                    now()->endOfMonth()->toDateString()
                ]
            );
        }

        $cuts = $model->latest()->get();

        return [
            'summary' => $cuts->count()
                . ' mess cut record(s) this month.',
            'columns' => ['ID', 'Date'],
            'rows' => $cuts->map(fn ($c) => [
                $c->id,
                $c->cut_date ?? $c->created_at?->format('d M Y') ?? '-',
            ])->toArray(),
        ];
    }

    private function monthlyMessBills(): array
    {
        $model = \App\Models\MessBill::query();

        $table = $model->getModel()->getTable();

        if (\Illuminate\Support\Facades\Schema::hasColumn($table, 'bill_date')) {
            $model->whereBetween(
                'bill_date',
                [
                    now()->startOfMonth()->toDateString(),
                    now()->endOfMonth()->toDateString()
                ]
            );
        } elseif (\Illuminate\Support\Facades\Schema::hasColumn($table, 'created_at')) {
            $model->whereBetween(
                'created_at',
                [
                    now()->startOfMonth(),
                    now()->endOfMonth()
                ]
            );
        }

        $bills = $model->latest()->get();

        $total = $bills->sum('amount');

        return [
            'summary' => $bills->count()
                . ' mess bill(s) this month. Total: ₹'
                . number_format($total, 2),
            'columns' => ['ID', 'Amount'],
            'rows' => $bills->map(fn ($b) => [
                $b->id,
                '₹' . number_format($b->amount ?? 0, 2),
            ])->toArray(),
        ];
    }
}
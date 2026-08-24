<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\MessCut;
use App\Models\MessRate;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MessBillController extends Controller
{
    public function create()
    {
        return view('mess-bills.create');
    }

    // Calculates attended days per active+allocated student for the chosen month,
    // subtracting any days covered by an approved mess cut, then creates one
    // Invoice per student (amount = attended_days * hostel's rate_per_day).
    public function generate(Request $request)
    {
        $data = $request->validate([
            'month' => ['required', 'date_format:Y-m'],
        ]);

        $start = Carbon::createFromFormat('Y-m', $data['month'])->startOfMonth();
        $end = (clone $start)->endOfMonth();
        $totalDays = $start->daysInMonth;

        $students = Student::with('currentAllocation.room.floor.block.hostel')
            ->where('status', 'active')
            ->get()
            ->filter(fn ($s) => $s->currentAllocation && $s->currentAllocation->room);

        $created = 0;
        $skipped = 0;

        DB::transaction(function () use ($students, $start, $end, $totalDays, &$created, &$skipped) {
            foreach ($students as $student) {
                $hostelId = $student->currentAllocation->room->floor->block->hostel_id;
                $rate = MessRate::where('hostel_id', $hostelId)->value('rate_per_day');

                if (! $rate) {
                    $skipped++;
                    continue; // no rate configured for this student's hostel — skip rather than guess
                }

                // Sum days this student was on a mess cut that overlaps the billing month
                $cutDays = MessCut::where('student_id', $student->id)
                    ->where('from_date', '<=', $end)
                    ->where('to_date', '>=', $start)
                    ->get()
                    ->sum(function ($cut) use ($start, $end) {
                        $overlapStart = $cut->from_date->max($start);
                        $overlapEnd = $cut->to_date->min($end);

                        return $overlapStart->diffInDays($overlapEnd) + 1;
                    });

                $attendedDays = max(0, $totalDays - $cutDays);
                $amount = round($attendedDays * $rate, 2);

                if ($amount <= 0) {
                    $skipped++;
                    continue;
                }

                // Avoid duplicate mess bills for the same student + period
                $period = $start->format('F Y');
                $exists = Invoice::where('student_id', $student->id)
                    ->where('period', "Mess Fee - {$period}")
                    ->exists();

                if ($exists) {
                    $skipped++;
                    continue;
                }

                Invoice::create([
                    'invoice_no' => 'MSS-' . now()->format('Ymd') . '-' . Str::upper(Str::random(5)),
                    'student_id' => $student->id,
                    'period' => "Mess Fee - {$period}",
                    'amount' => $amount,
                    'due_date' => (clone $end)->addDays(7),
                    'status' => 'unpaid',
                    'remarks' => "{$attendedDays} attended day(s) x ₹{$rate}/day (mess cut: {$cutDays} day(s) deducted).",
                ]);

                $created++;
            }
        });

        return back()->with('status', "Mess bills generated: {$created} created, {$skipped} skipped (already billed or no rate set).");
    }
}
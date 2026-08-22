<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\CurfewSetting;
use App\Models\Hostel;
use App\Models\LeaveRequest;
use App\Models\Student;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    // Shows all active, allocated students for a given date with their marked status
    public function index(Request $request)
    {
        $date = $request->get('date', now()->format('Y-m-d'));

        $students = Student::where('status', 'active')
            ->with(['currentAllocation.room', 'attendanceOn' => function ($q) use ($date) {
                $q->where('date', $date);
            }])
            ->orderBy('name')
            ->get();

        // Students currently on approved leave for this date — pre-marked as on_leave
        $onLeaveIds = LeaveRequest::where('status', 'approved')
            ->whereDate('from_date', '<=', $date)
            ->whereDate('to_date', '>=', $date)
            ->pluck('student_id')
            ->toArray();

        return view('attendance.index', compact('students', 'date', 'onLeaveIds'));
    }

    // AJAX: mark/update a single student's attendance for a date — instant save
    public function mark(Request $request)
    {
        $data = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'date' => ['required', 'date'],
            'status' => ['required', 'in:present,absent,on_leave'],
            'check_in_time' => ['nullable', 'date_format:H:i'],
        ]);

        $student = Student::with('currentAllocation.room.floor.block.hostel')->findOrFail($data['student_id']);

        $isLate = false;
        if (! empty($data['check_in_time']) && $data['status'] === 'present') {
            $hostelId = $student->currentAllocation?->room?->floor?->block?->hostel_id;
            $curfew = $hostelId ? CurfewSetting::where('hostel_id', $hostelId)->first() : null;
            $cutoff = $curfew->curfew_time ?? '22:00:00';
            $isLate = $data['check_in_time'] > substr($cutoff, 0, 5);
        }

        $attendance = Attendance::updateOrCreate(
            ['student_id' => $data['student_id'], 'date' => $data['date']],
            [
                'status' => $data['status'],
                'check_in_time' => $data['check_in_time'] ?? null,
                'is_late' => $isLate,
                'marked_by' => $request->user()->id,
            ]
        );

        return response()->json([
            'success' => true,
            'status' => $attendance->status,
            'is_late' => $attendance->is_late,
        ]);
    }

    // AJAX: bulk mark all currently-unmarked students as present for the date
    public function markAllPresent(Request $request)
    {
        $data = $request->validate(['date' => ['required', 'date']]);

        $markedStudentIds = Attendance::where('date', $data['date'])->pluck('student_id');

        $students = Student::where('status', 'active')
            ->whereNotIn('id', $markedStudentIds)
            ->get();

        foreach ($students as $student) {
            Attendance::create([
                'student_id' => $student->id,
                'date' => $data['date'],
                'status' => 'present',
                'marked_by' => $request->user()->id,
            ]);
        }

        return response()->json(['success' => true, 'marked_count' => $students->count()]);
    }

    public function curfewSettings()
    {
        $hostels = Hostel::active()->get();
        $settings = CurfewSetting::pluck('curfew_time', 'hostel_id');

        return view('attendance.curfew-settings', compact('hostels', 'settings'));
    }

    public function saveCurfewSettings(Request $request)
    {
        $data = $request->validate([
            'curfew_times' => ['required', 'array'],
            'curfew_times.*' => ['required', 'date_format:H:i'],
        ]);

        foreach ($data['curfew_times'] as $hostelId => $time) {
            CurfewSetting::updateOrCreate(['hostel_id' => $hostelId], ['curfew_time' => $time]);
        }

        return back()->with('status', 'Curfew settings updated successfully.');
    }
}
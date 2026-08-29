<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\CurfewSetting;
use App\Models\Student;
use Illuminate\Http\Request;

class QrAttendanceController extends Controller
{
    // Camera-based scanner page for gate staff
    public function scanner()
    {
        return view('attendance.qr-scanner');
    }

    // AJAX: called the instant the scanner decodes a QR code.
    // token -> identify student -> mark today's attendance -> respond.
    public function scan(Request $request)
    {
        $data = $request->validate([
            'token' => ['required', 'string'],
        ]);

        $student = Student::with('currentAllocation.room.floor.block.hostel')
            ->where('qr_token', $data['token'])
            ->first();

        if (! $student) {
            return response()->json([
                'success' => false,
                'message' => 'QR code not recognized. This may be an invalid or old ID card.',
            ], 404);
        }

        if ($student->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => "{$student->name}'s account is not active. Contact the hostel office.",
            ], 403);
        }

        $now = now();
        $checkInTime = $now->format('H:i');

        // Same curfew logic as manual attendance marking, so QR check-ins
        // are flagged "late" consistently with the roll-call page.
        $hostelId = $student->currentAllocation?->room?->floor?->block?->hostel_id;
        $curfew = $hostelId ? CurfewSetting::where('hostel_id', $hostelId)->first() : null;
        $cutoff = $curfew->curfew_time ?? '22:00:00';
        $isLate = $checkInTime > substr($cutoff, 0, 5);

        $alreadyMarkedToday = Attendance::where('student_id', $student->id)
            ->where('date', $now->format('Y-m-d'))
            ->exists();

        $attendance = Attendance::updateOrCreate(
            ['student_id' => $student->id, 'date' => $now->format('Y-m-d')],
            [
                'status' => 'present',
                'check_in_time' => $checkInTime,
                'is_late' => $isLate,
                'marked_by' => $request->user()->id,
            ]
        );

        return response()->json([
            'success' => true,
            'student_name' => $student->name,
            'student_uid' => $student->student_uid,
            'room' => $student->currentAllocation?->room?->room_number,
            'is_late' => $attendance->is_late,
            'already_marked' => $alreadyMarkedToday,
            'message' => $alreadyMarkedToday
                ? "{$student->name}'s attendance was already recorded today — check-in time updated."
                : 'Attendance marked successfully.',
        ]);
    }

    // Printable ID card with embedded QR code
    public function idCard(Student $student)
    {
        $student->ensureQrToken();

        return view('students.id-card', compact('student'));
    }
}
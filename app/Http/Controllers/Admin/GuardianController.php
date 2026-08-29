<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guardian;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\ActivityLogger;

class GuardianController extends Controller
{
    public function store(Request $request, Student $student)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'relation' => ['required', 'in:father,mother,brother,sister,uncle,other'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'occupation' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'is_primary' => ['nullable', 'boolean'],
        ]);

        $guardian = null;

        DB::transaction(function () use ($student, $data, &$guardian) {
            if (! empty($data['is_primary'])) {
                $student->guardians()->update(['is_primary' => false]);
            }

            $guardian = $student->guardians()->create($data);
        });

        ActivityLogger::log(
            action: 'created',
            module: 'guardians',
            description: "Guardian {$guardian->name} added for student ID {$student->id}",
            subject: $guardian,
            newValues: $guardian->toArray()
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'guardians' => $student->guardians()->get()
            ]);
        }

        return back()->with('status', 'Guardian added successfully.');
    }

    public function destroy(Guardian $guardian)
    {
        $oldValues = $guardian->toArray();
        $guardianName = $guardian->name;
        $studentId = $guardian->student_id;

        $guardian->delete();

        ActivityLogger::log(
            action: 'deleted',
            module: 'guardians',
            description: "Guardian {$guardianName} removed from student ID {$studentId}",
            subject: $guardian,
            oldValues: $oldValues
        );

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('status', 'Guardian removed successfully.');
    }
}
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guardian;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        DB::transaction(function () use ($student, $data) {
            if (! empty($data['is_primary'])) {
                $student->guardians()->update(['is_primary' => false]);
            }

            $student->guardians()->create($data);
        });

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'guardians' => $student->guardians()->get()]);
        }

        return back()->with('status', 'Guardian added successfully.');
    }

    public function destroy(Guardian $guardian)
    {
        $guardian->delete();

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('status', 'Guardian removed successfully.');
    }
}
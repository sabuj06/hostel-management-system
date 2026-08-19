<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $students = Student::with('currentAllocation.room')
            ->when($request->search, function ($q) use ($request) {
                $q->where(function ($q2) use ($request) {
                    $q2->where('name', 'like', "%{$request->search}%")
                        ->orWhere('student_uid', 'like', "%{$request->search}%")
                        ->orWhere('phone', 'like', "%{$request->search}%");
                });
            })
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('students.index', compact('students'));
    }

    public function create()
    {
        return view('students.create');
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['student_uid'] = $data['student_uid'] ?? $this->generateUid();

        $student = Student::create($data);

        return redirect()->route('students.show', $student)->with('status', 'Student added successfully. Now you can add guardian(s).');
    }

    public function show(Student $student)
    {
        $student->load('guardians', 'allocations.room', 'allocations.bed');

        return view('students.show', compact('student'));
    }

    public function edit(Student $student)
    {
        return view('students.edit', compact('student'));
    }

    public function update(Request $request, Student $student)
    {
        $data = $this->validated($request, $student->id);
        $student->update($data);

        return redirect()->route('students.show', $student)->with('status', 'Student updated successfully.');
    }

    public function destroy(Student $student)
    {
        $student->delete();

        return redirect()->route('students.index')->with('status', 'Student deleted successfully.');
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'student_uid' => ['nullable', 'string', 'max:50', 'unique:students,student_uid,' . $ignoreId],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'gender' => ['nullable', 'in:male,female,other'],
            'date_of_birth' => ['nullable', 'date'],
            'course' => ['nullable', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:255'],
            'session' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'admission_date' => ['nullable', 'date'],
            'status' => ['required', 'in:active,inactive,left'],
        ]);
    }

    private function generateUid(): string
    {
        do {
            $uid = 'STU-' . now()->format('y') . '-' . Str::upper(Str::random(5));
        } while (Student::where('student_uid', $uid)->exists());

        return $uid;
    }
}
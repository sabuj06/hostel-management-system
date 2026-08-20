<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Visitor;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class VisitorController extends Controller
{
    public function index(Request $request)
    {
        $visitors = Visitor::with('student', 'approvedBy')
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->search, function ($q) use ($request) {
                $q->where('visitor_name', 'like', "%{$request->search}%")
                    ->orWhereHas('student', fn ($q2) => $q2->where('name', 'like', "%{$request->search}%"));
            })
            ->when($request->date, fn ($q) => $q->whereDate('check_in_time', $request->date))
            ->latest('check_in_time')
            ->paginate(10)
            ->withQueryString();

        $stats = [
            'currently_in' => Visitor::currentlyIn()->count(),
            'today_total' => Visitor::whereDate('check_in_time', now())->count(),
        ];

        return view('visitors.index', compact('visitors', 'stats'));
    }

    public function create()
    {
        return view('visitors.create');
    }

    // AJAX: live student search (typeahead) — returns matching students as JSON
    public function searchStudents(Request $request)
    {
        $term = $request->get('q', '');

        $students = Student::where('status', 'active')
            ->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('student_uid', 'like', "%{$term}%");
            })
            ->limit(10)
            ->get(['id', 'name', 'student_uid']);

        return response()->json($students);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'visitor_name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'relation' => ['required', 'in:father,mother,brother,sister,relative,friend,other'],
            'purpose' => ['nullable', 'string', 'max:255'],
            'id_proof_type' => ['nullable', 'string', 'max:100'],
            'id_proof_number' => ['nullable', 'string', 'max:100'],
            'total_visitors' => ['required', 'integer', 'min:1', 'max:20'],
            'remarks' => ['nullable', 'string'],
        ]);

        Visitor::create([
            ...$data,
            'gate_pass_no' => $this->generateGatePassNo(),
            'check_in_time' => now(),
            'status' => 'checked_in',
            'approved_by' => $request->user()->id,
        ]);

        return redirect()->route('visitors.index')->with('status', 'Visitor checked in successfully.');
    }

    // AJAX: check a visitor out without leaving the list page
    public function checkout(Visitor $visitor)
    {
        if ($visitor->status === 'checked_out') {
            return response()->json(['success' => false, 'message' => 'Visitor already checked out.'], 422);
        }

        $visitor->update([
            'status' => 'checked_out',
            'check_out_time' => now(),
        ]);

        return response()->json([
            'success' => true,
            'check_out_time' => $visitor->check_out_time->format('d M Y, h:i A'),
        ]);
    }

    private function generateGatePassNo(): string
    {
        do {
            $no = 'GP-' . now()->format('ymd') . '-' . Str::upper(Str::random(4));
        } while (Visitor::where('gate_pass_no', $no)->exists());

        return $no;
    }
}
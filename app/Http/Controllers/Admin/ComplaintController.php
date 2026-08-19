<?php

namespace App\Http\Controllers\Admin;

use App\Models\Complaint;
use App\Models\ComplaintCategory;
use App\Models\Role;
use App\Models\Student;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ComplaintController extends Controller
{
    public function index(Request $request)
    {
        $complaints = Complaint::with('student', 'category', 'assignedTo')
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->priority, fn ($q) => $q->where('priority', $request->priority))
            ->when($request->search, function ($q) use ($request) {
                $q->where('title', 'like', "%{$request->search}%")
                    ->orWhere('ticket_no', 'like', "%{$request->search}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $counts = [
            'open' => Complaint::where('status', 'open')->count(),
            'in_progress' => Complaint::where('status', 'in_progress')->count(),
            'resolved' => Complaint::where('status', 'resolved')->count(),
        ];

        return view('complaints.index', compact('complaints', 'counts'));
    }

    public function create()
    {
        $students = Student::where('status', 'active')->get();
        $categories = ComplaintCategory::all();

        return view('complaints.create', compact('students', 'categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'complaint_category_id' => ['nullable', 'exists:complaint_categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'priority' => ['required', 'in:low,medium,high,urgent'],
        ]);

        $student = Student::findOrFail($data['student_id']);
        $roomId = optional($student->currentAllocation())->room_id;

        Complaint::create([
            ...$data,
            'ticket_no' => $this->generateTicketNo(),
            'room_id' => $roomId,
            'status' => 'open',
        ]);

        return redirect()->route('complaints.index')->with('status', 'Complaint logged successfully.');
    }

    public function show(Complaint $complaint)
    {
        $complaint->load('student', 'category', 'room', 'assignedTo', 'comments.user');

        // Staff/warden users eligible for assignment
        $assignees = User::whereHas('role', fn ($q) => $q->whereIn('name', ['warden', 'staff']))
            ->where('status', 'active')
            ->get();

        return view('complaints.show', compact('complaint', 'assignees'));
    }

    // AJAX: update status/priority/assignee inline from the show page
    public function updateStatus(Request $request, Complaint $complaint)
    {
        $data = $request->validate([
            'status' => ['required', 'in:open,in_progress,resolved,closed,rejected'],
        ]);

        $complaint->update([
            'status' => $data['status'],
            'resolved_at' => in_array($data['status'], ['resolved', 'closed']) ? now() : null,
        ]);

        return response()->json(['success' => true, 'status' => $complaint->status]);
    }

    public function assign(Request $request, Complaint $complaint)
    {
        $data = $request->validate(['assigned_to' => ['nullable', 'exists:users,id']]);

        $complaint->update($data);

        $assignedName = $complaint->assignedTo?->name ?? 'Unassigned';

        return response()->json(['success' => true, 'assigned_name' => $assignedName]);
    }

    // AJAX: add a comment/update to the complaint thread
    public function addComment(Request $request, Complaint $complaint)
    {
        $data = $request->validate(['comment' => ['required', 'string']]);

        $comment = $complaint->comments()->create([
            'user_id' => $request->user()->id,
            'comment' => $data['comment'],
        ]);

        return response()->json([
            'success' => true,
            'comment' => [
                'id' => $comment->id,
                'comment' => $comment->comment,
                'user_name' => $request->user()->name,
                'created_at' => $comment->created_at->format('d M Y, h:i A'),
            ],
        ]);
    }

    private function generateTicketNo(): string
    {
        do {
            $no = 'TCK-' . now()->format('ymd') . '-' . Str::upper(Str::random(4));
        } while (Complaint::where('ticket_no', $no)->exists());

        return $no;
    }
}
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;

class LeaveRequestController extends Controller
{
    public function index(Request $request)
    {
        $leaveRequests = LeaveRequest::with(['student', 'reviewedBy'])
            ->when($request->status, function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.leave-requests.index', compact('leaveRequests'));
    }

    public function review(Request $request, LeaveRequest $leaveRequest)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:approved,rejected'],
            'review_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $leaveRequest->update([
            'status' => $validated['status'],
            'reviewed_by' => auth()->id(),
            'review_note' => $validated['review_note'] ?? null,
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Leave request reviewed successfully.');
    }
}
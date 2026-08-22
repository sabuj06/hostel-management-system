@extends('layouts.app')

@section('title', 'Leave Requests')
@section('page-title', 'Leave Requests')

@section('content')

<div class="card shadow-sm border-0">

    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Leave Requests</h5>

        <form method="GET" action="{{ route('leave-requests.index') }}">
            <select name="status"
                    class="form-select form-select-sm"
                    onchange="this.form.submit()">

                <option value="">All Status</option>

                <option value="pending"
                    {{ request('status') === 'pending' ? 'selected' : '' }}>
                    Pending
                </option>

                <option value="approved"
                    {{ request('status') === 'approved' ? 'selected' : '' }}>
                    Approved
                </option>

                <option value="rejected"
                    {{ request('status') === 'rejected' ? 'selected' : '' }}>
                    Rejected
                </option>

            </select>
        </form>
    </div>

    @if(session('success'))
        <div class="alert alert-success m-3 mb-0">
            {{ session('success') }}
        </div>
    @endif

    <div class="table-responsive">

        <table class="table table-bordered table-hover align-middle mb-0">

            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Student</th>
                    <th>From</th>
                    <th>To</th>
                    <th>Duration</th>
                    <th>Reason</th>
                    <th>Status</th>
                    <th width="280">Action</th>
                </tr>
            </thead>

            <tbody>

            @forelse($leaveRequests as $leaveRequest)

                <tr>

                    <td>
                        {{ $loop->iteration + ($leaveRequests->currentPage() - 1) * $leaveRequests->perPage() }}
                    </td>

                    <td>
                        <strong>
                            {{ $leaveRequest->student?->name ?? 'N/A' }}
                        </strong>
                    </td>

                    <td>
                        {{ $leaveRequest->from_date?->format('d M Y') }}
                    </td>

                    <td>
                        {{ $leaveRequest->to_date?->format('d M Y') }}
                    </td>

                    <td>
                        {{ $leaveRequest->durationInDays() }} days
                    </td>

                    <td>
                        {{ $leaveRequest->reason }}
                    </td>

                    <td>

                        @if($leaveRequest->status === 'pending')
                            <span class="badge bg-warning text-dark">
                                Pending
                            </span>

                        @elseif($leaveRequest->status === 'approved')
                            <span class="badge bg-success">
                                Approved
                            </span>

                        @elseif($leaveRequest->status === 'rejected')
                            <span class="badge bg-danger">
                                Rejected
                            </span>

                        @else
                            <span class="badge bg-secondary">
                                {{ ucfirst($leaveRequest->status) }}
                            </span>
                        @endif

                    </td>

                    <td>

                        @if($leaveRequest->status === 'pending')

                            <form method="POST"
                                  action="{{ route('leave-requests.review', $leaveRequest) }}">

                                @csrf

                                <div class="mb-2">
                                    <select name="status"
                                            class="form-select form-select-sm"
                                            required>

                                        <option value="">Select Action</option>
                                        <option value="approved">Approve</option>
                                        <option value="rejected">Reject</option>

                                    </select>
                                </div>

                                <div class="mb-2">
                                    <textarea name="review_note"
                                              class="form-control form-control-sm"
                                              rows="2"
                                              placeholder="Review note (optional)"></textarea>
                                </div>

                                <button type="submit"
                                        class="btn btn-primary btn-sm w-100">
                                    Submit Review
                                </button>

                            </form>

                        @else

                            <div class="small text-muted">

                                @if($leaveRequest->reviewedBy)
                                    Reviewed by:
                                    <strong>
                                        {{ $leaveRequest->reviewedBy->name }}
                                    </strong>
                                    <br>
                                @endif

                                @if($leaveRequest->reviewed_at)
                                    {{ $leaveRequest->reviewed_at->format('d M Y, h:i A') }}
                                @endif

                                @if($leaveRequest->review_note)
                                    <div class="mt-1">
                                        <strong>Note:</strong>
                                        {{ $leaveRequest->review_note }}
                                    </div>
                                @endif

                            </div>

                        @endif

                    </td>

                </tr>

            @empty

                <tr>
                    <td colspan="8" class="text-center py-4 text-muted">
                        No leave requests found.
                    </td>
                </tr>

            @endforelse

            </tbody>

        </table>

    </div>

    @if($leaveRequests->hasPages())
        <div class="card-footer bg-white">
            {{ $leaveRequests->links() }}
        </div>
    @endif

</div>

@endsection
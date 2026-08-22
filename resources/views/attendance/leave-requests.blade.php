@extends('layouts.app')

@section('title', 'Leave Requests')
@section('page-title', 'Leave Requests')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <form method="GET" class="d-flex gap-2">
        <select name="status" class="form-select" onchange="this.form.submit()">
            <option value="">All Status</option>
            <option value="pending" @selected(request('status') === 'pending')>Pending ({{ $pendingCount }})</option>
            <option value="approved" @selected(request('status') === 'approved')>Approved</option>
            <option value="rejected" @selected(request('status') === 'rejected')>Rejected</option>
        </select>
    </form>
    <a href="{{ route('attendance.index') }}" class="btn btn-outline-secondary"><i class="bi bi-calendar-check"></i> Attendance</a>
</div>

<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Student</th>
                    <th>From</th>
                    <th>To</th>
                    <th>Reason</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody id="leave-tbody">
                @forelse($leaveRequests as $lr)
                <tr data-leave-id="{{ $lr->id }}">
                    <td>
                        <a href="{{ route('students.show', $lr->student) }}">{{ $lr->student->name }}</a>
                        <div class="small text-muted">{{ $lr->student->student_uid }}</div>
                    </td>
                    <td>{{ $lr->from_date->format('d M Y') }}</td>
                    <td>{{ $lr->to_date->format('d M Y') }}</td>
                    <td>{{ $lr->reason }}</td>
                    <td class="status-cell">
                        <span class="badge bg-{{ $lr->status === 'approved' ? 'success' : ($lr->status === 'rejected' ? 'danger' : 'secondary') }} text-capitalize">
                            {{ $lr->status }}
                        </span>
                    </td>
                    <td class="text-end action-cell">
                        @if($lr->status === 'pending')
                            <button class="btn btn-sm btn-outline-success review-btn" data-leave-id="{{ $lr->id }}" data-decision="approved">Approve</button>
                            <button class="btn btn-sm btn-outline-danger review-btn" data-leave-id="{{ $lr->id }}" data-decision="rejected">Reject</button>
                        @else
                            <span class="small text-muted">by {{ $lr->reviewedBy->name ?? '-' }}</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No leave requests found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">{{ $leaveRequests->links() }}</div>
</div>
@endsection

@push('scripts')
<script>
    $(function () {
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

        // Approve/Reject via AJAX — row updates instantly, no reload
        $(document).on('click', '.review-btn', function () {
            const $btn = $(this);
            const leaveId = $btn.data('leave-id');
            const decision = $btn.data('decision');
            const $row = $(`tr[data-leave-id="${leaveId}"]`);

            let note = null;
            if (decision === 'rejected') {
                note = prompt('Optional: reason for rejection');
            }

            $row.find('.review-btn').prop('disabled', true);

            $.ajax({
                url: `/leave-requests/${leaveId}/review`,
                method: 'POST',
                data: { status: decision, review_note: note },
                success: function (res) {
                    const badgeClass = res.status === 'approved' ? 'bg-success' : 'bg-danger';
                    $row.find('.status-cell').html(`<span class="badge ${badgeClass} text-capitalize">${res.status}</span>`);
                    $row.find('.action-cell').html(`<span class="small text-muted">by ${res.reviewed_by}</span>`);
                },
                error: function () {
                    alert('Failed to update leave request.');
                    $row.find('.review-btn').prop('disabled', false);
                }
            });
        });
    });
</script>
@endpush
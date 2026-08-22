@extends('layouts.student')

@section('title', 'My Leave Requests')
@section('page-title', 'My Leave Requests')

@section('content')
<div class="d-flex justify-content-end mb-3">
    <a href="{{ route('student-portal.leave-requests.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Request Leave</a>
</div>

<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr><th>From</th><th>To</th><th>Reason</th><th>Status</th><th>Note</th></tr>
            </thead>
            <tbody>
                @forelse($leaveRequests as $lr)
                <tr>
                    <td>{{ $lr->from_date->format('d M Y') }}</td>
                    <td>{{ $lr->to_date->format('d M Y') }}</td>
                    <td>{{ $lr->reason }}</td>
                    <td>
                        <span class="badge bg-{{ $lr->status === 'approved' ? 'success' : ($lr->status === 'rejected' ? 'danger' : 'secondary') }} text-capitalize">
                            {{ $lr->status }}
                        </span>
                    </td>
                    <td class="small text-muted">{{ $lr->review_note ?? '-' }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-muted py-4">No leave requests yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">{{ $leaveRequests->links() }}</div>
</div>
@endsection
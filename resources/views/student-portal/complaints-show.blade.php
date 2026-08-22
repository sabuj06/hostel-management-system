@extends('layouts.student')

@section('title', 'Complaint ' . $complaint->ticket_no)
@section('page-title', 'Complaint Details')

@section('content')
<div class="card shadow-sm border-0" style="max-width:750px;">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start mb-3">
            <div>
                <h6 class="mb-1">{{ $complaint->title }}</h6>
                <div class="text-muted small">{{ $complaint->ticket_no }} &middot; {{ $complaint->created_at->format('d M Y, h:i A') }}</div>
            </div>
            <span class="badge bg-{{ $complaint->isClosed() ? 'success' : 'info' }} text-capitalize">{{ str_replace('_', ' ', $complaint->status) }}</span>
        </div>

        <p>{{ $complaint->description }}</p>

        <div class="row small text-muted mb-3">
            <div class="col-6"><strong>Category:</strong> {{ $complaint->category->name ?? 'Uncategorized' }}</div>
            <div class="col-6"><strong>Priority:</strong> <span class="text-capitalize">{{ $complaint->priority }}</span></div>
        </div>

        @if($complaint->assignedTo)
            <div class="alert alert-light border small mb-3">
                Assigned to <strong>{{ $complaint->assignedTo->name }}</strong> for resolution.
            </div>
        @endif

        <hr>

        <h6 class="mb-3">Updates</h6>
        @forelse($complaint->comments as $comment)
        <div class="border-bottom pb-2 mb-2">
            <div class="small text-muted">{{ $comment->user->name ?? 'Staff' }} &middot; {{ $comment->created_at->format('d M Y, h:i A') }}</div>
            <div>{{ $comment->comment }}</div>
        </div>
        @empty
        <p class="text-muted small mb-0">No updates yet. The hostel team will respond soon.</p>
        @endforelse
    </div>
</div>

<a href="{{ route('student-portal.complaints') }}" class="btn btn-light mt-3">Back to My Complaints</a>
@endsection
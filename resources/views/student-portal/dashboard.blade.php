@extends('layouts.student')

@section('title', 'Dashboard')
@section('page-title', 'My Dashboard')

@section('content')
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="text-muted small">Open Complaints</div>
                <div class="fs-3 fw-bold text-warning">{{ $stats['open_complaints'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="text-muted small">Unpaid Invoices</div>
                <div class="fs-3 fw-bold text-danger">{{ $stats['unpaid_invoices'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="text-muted small">Total Due</div>
                <div class="fs-3 fw-bold">৳{{ number_format($stats['total_due'], 2) }}</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-5">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h6 class="mb-3">My Room</h6>
                @if($student->currentAllocation)
                    <p class="mb-1"><strong>Room:</strong> {{ $student->currentAllocation->room->room_number }}</p>
                    <p class="mb-1"><strong>Bed:</strong> {{ $student->currentAllocation->bed->bed_number }}</p>
                    <p class="mb-0 text-muted small">Since {{ $student->currentAllocation->allocated_date->format('d M Y') }}</p>
                @else
                    <p class="text-muted mb-0">You have not been allocated a room yet. Please contact the hostel office.</p>
                @endif
            </div>
        </div>

        <div class="card shadow-sm border-0 mt-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Need help?</h6>
                    <a href="{{ route('student-portal.complaints.create') }}" class="btn btn-sm btn-primary">
                        <i class="bi bi-plus-lg"></i> Log Complaint
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-7">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h6 class="mb-3">Latest Notices</h6>
                @forelse($notices as $notice)
                <div class="border-bottom pb-2 mb-2">
                    <div class="fw-semibold">{{ $notice->title }}</div>
                    <div class="small text-muted">{{ optional($notice->publish_date)->format('d M Y') }}</div>
                </div>
                @empty
                <p class="text-muted mb-0">No notices yet.</p>
                @endforelse
                <a href="{{ route('notices.index') }}" class="btn btn-sm btn-outline-secondary mt-2">View All Notices</a>
            </div>
        </div>
    </div>
</div>
@endsection
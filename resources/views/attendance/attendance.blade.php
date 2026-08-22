@extends('layouts.student')

@section('title', 'My Attendance')
@section('page-title', 'My Attendance & Curfew Record')

@section('content')
<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="card shadow-sm border-0"><div class="card-body">
            <div class="text-muted small">Present</div>
            <div class="fs-4 text-success fw-bold">{{ $summary['present'] }}</div>
        </div></div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card shadow-sm border-0"><div class="card-body">
            <div class="text-muted small">Absent</div>
            <div class="fs-4 text-danger fw-bold">{{ $summary['absent'] }}</div>
        </div></div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card shadow-sm border-0"><div class="card-body">
            <div class="text-muted small">On Leave</div>
            <div class="fs-4 text-info fw-bold">{{ $summary['on_leave'] }}</div>
        </div></div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card shadow-sm border-0"><div class="card-body">
            <div class="text-muted small">Late Check-ins</div>
            <div class="fs-4 text-warning fw-bold">{{ $summary['late'] }}</div>
        </div></div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr><th>Date</th><th>Status</th><th>Check-in Time</th></tr>
            </thead>
            <tbody>
                @forelse($records as $r)
                <tr>
                    <td>{{ $r->date->format('d M Y (D)') }}</td>
                    <td>
                        <span class="badge bg-{{ $r->status === 'present' ? ($r->is_late ? 'warning' : 'success') : ($r->status === 'absent' ? 'danger' : 'info') }}">
                            {{ $r->is_late ? 'Late' : ucfirst(str_replace('_', ' ', $r->status)) }}
                        </span>
                    </td>
                    <td>{{ $r->check_in_time ? \Illuminate\Support\Carbon::parse($r->check_in_time)->format('h:i A') : '-' }}</td>
                </tr>
                @empty
                <tr><td colspan="3" class="text-center text-muted py-4">No attendance records yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">{{ $records->links() }}</div>
</div>
@endsection
@extends('layouts.app')

@section('title', 'My Attendance')
@section('page-title', 'My Attendance')

@section('content')

<div class="row g-3 mb-4">

    <div class="col-md-3">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="text-muted small">Total</div>
                <h3 class="mb-0">{{ $summary['total'] ?? 0 }}</h3>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="text-muted small">Present</div>
                <h3 class="mb-0 text-success">
                    {{ $summary['present'] ?? 0 }}
                </h3>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="text-muted small">Absent</div>
                <h3 class="mb-0 text-danger">
                    {{ $summary['absent'] ?? 0 }}
                </h3>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="text-muted small">Late</div>
                <h3 class="mb-0 text-warning">
                    {{ $summary['late'] ?? 0 }}
                </h3>
            </div>
        </div>
    </div>

</div>


<div class="card shadow-sm border-0">

    <div class="card-header bg-white">
        <h5 class="mb-0">
            Attendance History
        </h5>
    </div>

    <div class="table-responsive">

        <table class="table table-bordered align-middle mb-0">

            <thead class="table-light">
                <tr>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Late</th>
                </tr>
            </thead>

            <tbody>

                @forelse($records as $record)

                    <tr>

                        <td>
                            {{ optional($record->attendance_date)->format('d M Y') ?? $record->attendance_date }}
                        </td>

                        <td>
                            @if($record->status === 'present')
                                <span class="badge bg-success">
                                    Present
                                </span>

                            @elseif($record->status === 'absent')
                                <span class="badge bg-danger">
                                    Absent
                                </span>

                            @elseif($record->status === 'on_leave')
                                <span class="badge bg-info">
                                    On Leave
                                </span>

                            @else
                                <span class="badge bg-secondary">
                                    {{ ucfirst(str_replace('_', ' ', $record->status)) }}
                                </span>
                            @endif
                        </td>

                        <td>
                            @if($record->is_late)
                                <span class="badge bg-warning text-dark">
                                    Late
                                </span>
                            @else
                                <span class="text-muted">
                                    No
                                </span>
                            @endif
                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="3" class="text-center text-muted py-4">
                            No attendance records found.
                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</div>

@endsection
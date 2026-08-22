@extends('layouts.app')

@section('title', 'Curfew Settings')
@section('page-title', 'Curfew Settings')

@section('content')
<div class="card shadow-sm border-0" style="max-width:600px;">
    <div class="card-body">
        @if(session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('attendance.curfew-settings.save') }}">
            @csrf
            @forelse($hostels as $hostel)
            <div class="mb-3">
                <label class="form-label">{{ $hostel->name }} — Curfew Time</label>
                <input
                    type="time"
                    name="curfew_times[{{ $hostel->id }}]"
                    class="form-control"
                    value="{{ isset($settings[$hostel->id]) ? \Illuminate\Support\Carbon::parse($settings[$hostel->id])->format('H:i') : '22:00' }}"
                    required
                >
            </div>
            @empty
            <p class="text-muted">No hostels found. Add a hostel first.</p>
            @endforelse

            @if($hostels->isNotEmpty())
            <button class="btn btn-primary">Save Curfew Times</button>
            @endif
            <a href="{{ route('attendance.index') }}" class="btn btn-light">Back to Attendance</a>
        </form>
    </div>
</div>

<div class="alert alert-info mt-3 small" style="max-width:600px;">
    <i class="bi bi-info-circle"></i> When a student's check-in time (recorded during attendance marking) is later than the hostel's curfew time, they'll be automatically flagged as "Late".
</div>
@endsection
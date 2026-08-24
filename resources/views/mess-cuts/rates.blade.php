@extends('layouts.app')

@section('title', 'Mess Rates')
@section('page-title', 'Mess Rates')

@section('content')
<div class="card shadow-sm border-0" style="max-width:600px;">
    <div class="card-body">
        @if(session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('mess-cuts.rates.save') }}">
            @csrf
            @forelse($hostels as $hostel)
            <div class="mb-3">
                <label class="form-label">{{ $hostel->name }} — Rate per Day (₹)</label>
                <input type="number" step="0.01" name="rates[{{ $hostel->id }}]" class="form-control" value="{{ $rates[$hostel->id] ?? '' }}" required>
            </div>
            @empty
            <p class="text-muted">No hostels found.</p>
            @endforelse

            @if($hostels->isNotEmpty())
            <button class="btn btn-primary">Save Rates</button>
            @endif
            <a href="{{ route('mess-cuts.index') }}" class="btn btn-light">Back</a>
        </form>
    </div>
</div>

<div class="alert alert-info mt-3 small" style="max-width:600px;">
    <i class="bi bi-info-circle"></i> This rate covers all meals (breakfast + lunch + dinner) for one day. Used when generating monthly mess bills.
</div>
@endsection
@extends('layouts.app')

@section('title', 'Generate Mess Bills')
@section('page-title', 'Generate Mess Bills')

@section('content')
<div class="card shadow-sm border-0" style="max-width:600px;">
    <div class="card-body">
        @if(session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        <p class="text-muted small">
            This will calculate each allocated student's attended days for the selected month
            (total days minus any mess cut days) and create one mess-fee invoice per student,
            using each hostel's configured rate per day.
        </p>

        <form method="POST" action="{{ route('mess-bills.generate') }}" id="generateForm">
            @csrf
            <div class="mb-3">
                <label class="form-label">Month</label>
                <input type="month" name="month" class="form-control" value="{{ now()->format('Y-m') }}" required>
            </div>
            <button type="submit" class="btn btn-primary" id="generate-btn">
                <i class="bi bi-receipt"></i> Generate Mess Bills
            </button>
            <a href="{{ route('mess-cuts.index') }}" class="btn btn-light">Cancel</a>
        </form>
    </div>
</div>

<div class="alert alert-warning mt-3 small" style="max-width:600px;">
    <i class="bi bi-exclamation-triangle"></i> Make sure Mess Rates are set for each hostel before generating bills — students in hostels without a configured rate will be skipped.
</div>
@endsection

@push('scripts')
<script>
    $(function () {
        // Confirm before submitting — this creates real invoices
        $('#generateForm').on('submit', function (e) {
            const month = $('input[name=month]').val();
            if (!confirm(`Generate mess bills for ${month}? This will create invoices for all eligible students.`)) {
                e.preventDefault();
                return;
            }
            $('#generate-btn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Generating...');
        });
    });
</script>
@endpush
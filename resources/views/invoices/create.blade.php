@extends('layouts.app')

@section('title', 'Generate Invoice')
@section('page-title', 'Generate Invoice')

@section('content')
<div class="card shadow-sm border-0" style="max-width:750px;">
    <div class="card-body">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('invoices.store') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label">Fee Structure</label>
                <select name="fee_structure_id" id="fee_structure_id" class="form-select" required>
                    <option value="">Select Fee Structure</option>
                    @foreach($feeStructures as $fee)
                        <option value="{{ $fee->id }}" data-amount="{{ $fee->amount }}">
                            {{ $fee->name }} (₹{{ number_format($fee->amount, 2) }} / {{ str_replace('_', ' ', $fee->frequency) }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Students</label>
                <select name="student_ids[]" class="form-select" multiple size="6" required>
                    @foreach($students as $s)
                        <option value="{{ $s->id }}">{{ $s->student_uid }} - {{ $s->name }}</option>
                    @endforeach
                </select>
                <div class="form-text">Hold Ctrl (Windows) / Cmd (Mac) to select multiple students.</div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Period (optional)</label>
                    <input type="text" name="period" class="form-control" placeholder="e.g. August 2026" value="{{ old('period') }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Amount (per invoice)</label>
                    <input type="number" step="0.01" name="amount" id="amount" class="form-control" value="{{ old('amount') }}" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Due Date</label>
                <input type="date" name="due_date" class="form-control" value="{{ old('due_date', now()->addDays(7)->format('Y-m-d')) }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Remarks</label>
                <textarea name="remarks" class="form-control" rows="2">{{ old('remarks') }}</textarea>
            </div>

            <button type="submit" class="btn btn-primary">Generate Invoice(s)</button>
            <a href="{{ route('invoices.index') }}" class="btn btn-light">Cancel</a>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(function () {
        // Auto-fill amount field when a fee structure is selected
        $('#fee_structure_id').on('change', function () {
            const amount = $(this).find(':selected').data('amount');
            if (amount !== undefined) {
                $('#amount').val(amount);
            }
        });
    });
</script>
@endpush
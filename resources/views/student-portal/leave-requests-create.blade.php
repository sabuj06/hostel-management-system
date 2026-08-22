@extends('layouts.student')

@section('title', 'Request Leave')
@section('page-title', 'Request Leave')

@section('content')
<div class="card shadow-sm border-0" style="max-width:600px;">
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

        <form method="POST" action="{{ route('student-portal.leave-requests.store') }}">
            @csrf
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">From Date</label>
                    <input type="date" name="from_date" id="from_date" class="form-control" min="{{ now()->format('Y-m-d') }}" value="{{ old('from_date') }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">To Date</label>
                    <input type="date" name="to_date" id="to_date" class="form-control" min="{{ now()->format('Y-m-d') }}" value="{{ old('to_date') }}" required>
                </div>
            </div>

            <div id="duration-preview" class="alert alert-light border small" style="display:none;"></div>

            <div class="mb-3">
                <label class="form-label">Reason</label>
                <input type="text" name="reason" class="form-control" placeholder="e.g. Family emergency, festival" value="{{ old('reason') }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Additional Details (optional)</label>
                <textarea name="details" class="form-control" rows="3">{{ old('details') }}</textarea>
            </div>

            <button type="submit" class="btn btn-primary">Submit Request</button>
            <a href="{{ route('student-portal.leave-requests') }}" class="btn btn-light">Cancel</a>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(function () {
        // Live duration preview as dates are picked
        function updatePreview() {
            const from = $('#from_date').val();
            const to = $('#to_date').val();

            if (!from || !to) {
                $('#duration-preview').hide();
                return;
            }

            const fromDate = new Date(from);
            const toDate = new Date(to);
            const days = Math.round((toDate - fromDate) / (1000 * 60 * 60 * 24)) + 1;

            if (days < 1) {
                $('#duration-preview').text('"To Date" must be on or after "From Date".').removeClass('alert-light').addClass('alert-danger').show();
                return;
            }

            $('#duration-preview').text(`This request covers ${days} day(s).`).removeClass('alert-danger').addClass('alert-light').show();
        }

        $('#from_date, #to_date').on('change', updatePreview);
        updatePreview();
    });
</script>
@endpush
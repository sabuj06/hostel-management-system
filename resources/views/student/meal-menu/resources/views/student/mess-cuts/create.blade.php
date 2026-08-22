@extends('layouts.student')

@section('title', 'Request Mess Cut')
@section('page-title', 'Request Mess Cut')

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

        <form method="POST" action="{{ route('student-portal.mess-cuts.store') }}">
            @csrf
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">From Date</label>
                    <input type="date" name="from_date" id="from_date" class="form-control" min="{{ now()->format('Y-m-d') }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">To Date</label>
                    <input type="date" name="to_date" id="to_date" class="form-control" min="{{ now()->format('Y-m-d') }}" required>
                </div>
            </div>

            <div id="day-preview" class="alert alert-light border small" style="display:none;"></div>

            <div class="mb-3">
                <label class="form-label">Reason (optional)</label>
                <input type="text" name="reason" class="form-control" placeholder="e.g. Going home for festival">
            </div>

            <button type="submit" class="btn btn-primary">Submit Mess Cut</button>
            <a href="{{ route('student-portal.mess-cuts') }}" class="btn btn-light">Cancel</a>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(function () {
        function updatePreview() {
            const from = $('#from_date').val();
            const to = $('#to_date').val();
            if (!from || !to) { $('#day-preview').hide(); return; }

            const days = Math.round((new Date(to) - new Date(from)) / 86400000) + 1;
            if (days < 1) {
                $('#day-preview').text('"To Date" must be on or after "From Date".').show();
                return;
            }
            $('#day-preview').text(`${days} day(s) will be excluded from your mess bill.`).show();
        }
        $('#from_date, #to_date').on('change', updatePreview);
    });
</script>
@endpush
@extends('layouts.app')

@section('title', 'Add Mess Cut')
@section('page-title', 'Add Mess Cut')

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

        <form method="POST" action="{{ route('mess-cuts.store') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">Student</label>
                <select name="student_id" class="form-select" required>
                    <option value="">Select Student</option>
                    @foreach($students as $s)
                        <option value="{{ $s->id }}">{{ $s->student_uid }} - {{ $s->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">From Date</label>
                    <input type="date" name="from_date" id="from_date" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">To Date</label>
                    <input type="date" name="to_date" id="to_date" class="form-control" required>
                </div>
            </div>

            <div id="day-preview" class="alert alert-light border small" style="display:none;"></div>

            <div class="mb-3">
                <label class="form-label">Reason (optional)</label>
                <input type="text" name="reason" class="form-control" placeholder="e.g. Going home for festival">
            </div>

            <button type="submit" class="btn btn-primary">Save Mess Cut</button>
            <a href="{{ route('mess-cuts.index') }}" class="btn btn-light">Cancel</a>
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
            $('#day-preview').text(`This mess cut covers ${days} day(s) — those days will be excluded from the student's mess bill.`).show();
        }
        $('#from_date, #to_date').on('change', updatePreview);
    });
</script>
@endpush
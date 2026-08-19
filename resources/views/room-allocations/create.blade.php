@extends('layouts.app')

@section('title', 'Allocate Room')
@section('page-title', 'Allocate Room to Student')

@section('content')
<div class="card shadow-sm border-0" style="max-width:700px;">
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

        <form method="POST" action="{{ route('room-allocations.store') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label">Student</label>
                <select name="student_id" class="form-select" required>
                    <option value="">Select an unallocated student</option>
                    @foreach($students as $s)
                        <option value="{{ $s->id }}" @selected(old('student_id') == $s->id)>{{ $s->student_uid }} - {{ $s->name }}</option>
                    @endforeach
                </select>
                @if($students->isEmpty())
                    <div class="form-text text-danger">No unallocated active students available.</div>
                @endif
            </div>

            <div class="mb-3">
                <label class="form-label">Room</label>
                <select name="room_id" id="room_id" class="form-select" required>
                    <option value="">Select Room</option>
                    @foreach($rooms as $r)
                        <option value="{{ $r->id }}" @selected(old('room_id') == $r->id)>
                            {{ $r->room_number }} ({{ $r->available_beds_count }} beds available)
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Bed</label>
                <select name="bed_id" id="bed_id" class="form-select" required>
                    <option value="">Select room first</option>
                </select>
                <div class="form-text">Only available beds in the selected room are shown.</div>
            </div>

            <div class="mb-3">
                <label class="form-label">Allocation Date</label>
                <input type="date" name="allocated_date" class="form-control" value="{{ old('allocated_date', now()->format('Y-m-d')) }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Remarks</label>
                <textarea name="remarks" class="form-control" rows="2">{{ old('remarks') }}</textarea>
            </div>

            <button type="submit" class="btn btn-primary">Allocate</button>
            <a href="{{ route('room-allocations.index') }}" class="btn btn-light">Cancel</a>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(function () {
        // Load available beds via AJAX whenever the room selection changes
        $('#room_id').on('change', function () {
            const roomId = $(this).val();
            const $bedSelect = $('#bed_id');

            $bedSelect.html('<option value="">Loading beds...</option>');

            if (!roomId) {
                $bedSelect.html('<option value="">Select room first</option>');
                return;
            }

            $.getJSON(`/rooms/${roomId}/available-beds`, function (beds) {
                if (beds.length === 0) {
                    $bedSelect.html('<option value="">No available beds in this room</option>');
                    return;
                }
                let options = '<option value="">Select Bed</option>';
                beds.forEach(function (bed) {
                    options += `<option value="${bed.id}">Bed ${bed.bed_number}</option>`;
                });
                $bedSelect.html(options);
            }).fail(function () {
                $bedSelect.html('<option value="">Failed to load beds</option>');
            });
        });

        // Trigger on load in case of validation-error re-render with old('room_id')
        @if(old('room_id'))
            $('#room_id').trigger('change');
        @endif
    });
</script>
@endpush
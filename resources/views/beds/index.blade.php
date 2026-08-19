@extends('layouts.app')

@section('title', 'Beds')
@section('page-title', 'Bed Management')

@section('content')
<form method="GET" class="d-flex gap-2 mb-3">
    <select name="room_id" class="form-select" onchange="this.form.submit()">
        <option value="">All Rooms</option>
        @foreach($rooms as $r)
            <option value="{{ $r->id }}" @selected(request('room_id') == $r->id)>{{ $r->room_number }}</option>
        @endforeach
    </select>
    <select name="status" class="form-select" onchange="this.form.submit()">
        <option value="">All Status</option>
        <option value="available" @selected(request('status') === 'available')>Available</option>
        <option value="occupied" @selected(request('status') === 'occupied')>Occupied</option>
        <option value="maintenance" @selected(request('status') === 'maintenance')>Maintenance</option>
    </select>
</form>

<div class="row g-3" id="bed-grid">
    @forelse($beds as $bed)
    <div class="col-md-3 col-sm-4 col-6" data-bed-id="{{ $bed->id }}">
        <div class="card shadow-sm border-0 text-center py-3 bed-card">
            <div class="fs-4"><i class="bi bi-lamp"></i></div>
            <div class="fw-semibold">Bed {{ $bed->bed_number }}</div>
            <div class="small text-muted mb-2">Room {{ $bed->room->room_number }}</div>
            <select class="form-select form-select-sm bed-status-select mx-auto" style="width:auto;" data-bed-id="{{ $bed->id }}">
                <option value="available" @selected($bed->status === 'available')>Available</option>
                <option value="occupied" @selected($bed->status === 'occupied')>Occupied</option>
                <option value="maintenance" @selected($bed->status === 'maintenance')>Maintenance</option>
            </select>
        </div>
    </div>
    @empty
    <div class="col-12 text-center text-muted py-5">No beds found for this filter.</div>
    @endforelse
</div>

<div class="mt-3">{{ $beds->links() }}</div>
@endsection

@push('scripts')
<script>
    $(function () {
        $.ajaxSetup({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
        });

        // Live status update without page reload
        $(document).on('change', '.bed-status-select', function () {
            const $select = $(this);
            const bedId = $select.data('bed-id');
            const newStatus = $select.val();

            $select.prop('disabled', true);

            $.ajax({
                url: `/beds/${bedId}/status`,
                method: 'PATCH',
                data: { status: newStatus },
                success: function () {
                    $select.prop('disabled', false);
                    const toast = $('<div class="alert alert-success position-fixed top-0 end-0 m-3 py-2 px-3" style="z-index:2000;">Bed status updated</div>');
                    $('body').append(toast);
                    setTimeout(() => toast.fadeOut(300, () => toast.remove()), 1500);
                },
                error: function () {
                    $select.prop('disabled', false);
                    alert('Failed to update bed status. Please try again.');
                }
            });
        });
    });
</script>
@endpush
@extends('layouts.app')

@section('title', 'Visitors')
@section('page-title', 'Visitor Management')

@section('content')
<div class="row g-3 mb-3">
    <div class="col-md-3">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="text-muted small">Currently Inside</div>
                <div class="fs-3 fw-bold text-success">{{ $stats['currently_in'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="text-muted small">Today's Total Visits</div>
                <div class="fs-3 fw-bold">{{ $stats['today_total'] }}</div>
            </div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
    <form method="GET" class="d-flex gap-2">
        <input type="text" name="search" class="form-control" placeholder="Search visitor/student..." value="{{ request('search') }}">
        <input type="date" name="date" class="form-control" value="{{ request('date') }}">
        <select name="status" class="form-select" onchange="this.form.submit()">
            <option value="">All Status</option>
            <option value="checked_in" @selected(request('status') === 'checked_in')>Checked In</option>
            <option value="checked_out" @selected(request('status') === 'checked_out')>Checked Out</option>
        </select>
        <button class="btn btn-outline-secondary"><i class="bi bi-search"></i></button>
    </form>
    <a href="{{ route('visitors.create') }}" class="btn btn-primary"><i class="bi bi-person-plus"></i> New Check-in</a>
</div>

<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Gate Pass</th>
                    <th>Visitor</th>
                    <th>Visiting</th>
                    <th>Relation</th>
                    <th>Check-in</th>
                    <th>Check-out</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody id="visitors-tbody">
                @forelse($visitors as $visitor)
                <tr data-visitor-id="{{ $visitor->id }}">
                    <td>{{ $visitor->gate_pass_no }}</td>
                    <td>
                        {{ $visitor->visitor_name }}
                        @if($visitor->total_visitors > 1)
                            <span class="badge bg-light text-dark border">+{{ $visitor->total_visitors - 1 }}</span>
                        @endif
                        <div class="small text-muted">{{ $visitor->phone }}</div>
                    </td>
                    <td>
                        <a href="{{ route('students.show', $visitor->student) }}">{{ $visitor->student->name }}</a>
                        <div class="small text-muted">{{ $visitor->student->student_uid }}</div>
                    </td>
                    <td class="text-capitalize">{{ $visitor->relation }}</td>
                    <td>{{ $visitor->check_in_time->format('d M, h:i A') }}</td>
                    <td class="checkout-time-cell">{{ $visitor->check_out_time?->format('d M, h:i A') ?? '-' }}</td>
                    <td class="status-cell">
                        <span class="badge bg-{{ $visitor->status === 'checked_in' ? 'success' : 'secondary' }}">
                            {{ $visitor->status === 'checked_in' ? 'Checked In' : 'Checked Out' }}
                        </span>
                    </td>
                    <td class="text-end action-cell">
                        @if($visitor->status === 'checked_in')
                            <button class="btn btn-sm btn-outline-danger checkout-btn" data-visitor-id="{{ $visitor->id }}">
                                <i class="bi bi-box-arrow-right"></i> Checkout
                            </button>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted py-4">No visitor records found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">{{ $visitors->links() }}</div>
</div>
@endsection

@push('scripts')
<script>
    $(function () {
        $.ajaxSetup({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
        });

        // Checkout a visitor via AJAX — row updates instantly, no reload
        $(document).on('click', '.checkout-btn', function () {
            const $btn = $(this);
            const visitorId = $btn.data('visitor-id');
            const $row = $(`tr[data-visitor-id="${visitorId}"]`);

            $btn.prop('disabled', true).text('Checking out...');

            $.ajax({
                url: `/visitors/${visitorId}/checkout`,
                method: 'POST',
                success: function (res) {
                    $row.find('.checkout-time-cell').text(res.check_out_time);
                    $row.find('.status-cell').html('<span class="badge bg-secondary">Checked Out</span>');
                    $row.find('.action-cell').empty();
                },
                error: function () {
                    alert('Failed to checkout visitor.');
                    $btn.prop('disabled', false).html('<i class="bi bi-box-arrow-right"></i> Checkout');
                }
            });
        });
    });
</script>
@endpush
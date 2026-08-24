@extends('layouts.app')

@section('title', 'Smart Search')
@section('page-title', 'Natural-Language Search')

@section('content')
<div class="card shadow-sm border-0 mb-3">
    <div class="card-body">
        <form id="searchForm" class="d-flex gap-2">
            <input type="text" id="query-input" class="form-control" placeholder='e.g. "কাদের ফি বকেয়া রয়েছে?" or "which hostel has the most complaints?"' autocomplete="off">
            <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Ask</button>
        </form>
        <div class="small text-muted mt-2">
            Try: unpaid students, overdue invoices, room occupancy, complaints by hostel, open complaints.
        </div>
    </div>
</div>

<div id="results-area" style="display:none;">
    <div class="alert alert-light border" id="results-summary"></div>
    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="results-table">
                <thead class="table-light"><tr id="results-head"></tr></thead>
                <tbody id="results-body"></tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(function () {
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

        $('#searchForm').on('submit', function (e) {
            e.preventDefault();
            const query = $('#query-input').val().trim();
            if (!query) return;

            const $btn = $(this).find('button').prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

            $.ajax({
                url: '{{ route('smart-search.search') }}',
                method: 'POST',
                data: { query: query },
                success: function (res) {
                    $('#results-summary').text(res.summary);

                    const $head = $('#results-head').empty();
                    res.columns.forEach(col => $head.append(`<th>${col}</th>`));

                    const $body = $('#results-body').empty();
                    if (res.rows.length === 0) {
                        $body.append(`<tr><td colspan="${res.columns.length || 1}" class="text-center text-muted py-4">No matching records.</td></tr>`);
                    } else {
                        res.rows.forEach(row => {
                            const cells = row.map(v => `<td>${v}</td>`).join('');
                            $body.append(`<tr>${cells}</tr>`);
                        });
                    }

                    $('#results-area').show();
                },
                error: function () {
                    alert('Search failed. Please try again.');
                },
                complete: function () {
                    $btn.prop('disabled', false).html('<i class="bi bi-search"></i> Ask');
                }
            });
        });
    });
</script>
@endpush
@extends('layouts.app')

@section('title', 'New Visitor Check-in')
@section('page-title', 'New Visitor Check-in')

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

        <form method="POST" action="{{ route('visitors.store') }}">
            @csrf

            <div class="mb-3 position-relative">
                <label class="form-label">Visiting Student</label>
                <input type="text" id="student_search" class="form-control" placeholder="Type student name or UID..." autocomplete="off" value="{{ old('student_name') }}" required>
                <input type="hidden" name="student_id" id="student_id" value="{{ old('student_id') }}">
                <div id="student_results" class="list-group position-absolute w-100 shadow-sm" style="z-index:1000; display:none;"></div>
                <div class="form-text">Start typing to search — pick a student from the dropdown.</div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Visitor Name</label>
                    <input type="text" name="visitor_name" class="form-control" value="{{ old('visitor_name') }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Relation with Student</label>
                    <select name="relation" class="form-select" required>
                        @foreach(['father','mother','brother','sister','relative','friend','other'] as $rel)
                            <option value="{{ $rel }}" @selected(old('relation') === $rel)>{{ ucfirst($rel) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Total Visitors (incl. this one)</label>
                    <input type="number" name="total_visitors" class="form-control" min="1" max="20" value="{{ old('total_visitors', 1) }}" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Purpose of Visit</label>
                <input type="text" name="purpose" class="form-control" value="{{ old('purpose') }}">
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">ID Proof Type</label>
                    <input type="text" name="id_proof_type" class="form-control" placeholder="e.g. NID, Driving License" value="{{ old('id_proof_type') }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">ID Proof Number</label>
                    <input type="text" name="id_proof_number" class="form-control" value="{{ old('id_proof_number') }}">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Remarks</label>
                <textarea name="remarks" class="form-control" rows="2">{{ old('remarks') }}</textarea>
            </div>

            <button type="submit" class="btn btn-primary">Check In Visitor</button>
            <a href="{{ route('visitors.index') }}" class="btn btn-light">Cancel</a>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(function () {
        let searchTimer = null;

        // Live AJAX student search as the admin types (debounced)
        $('#student_search').on('input', function () {
            const term = $(this).val().trim();
            $('#student_id').val(''); // clear selection until a new pick is made

            clearTimeout(searchTimer);

            if (term.length < 2) {
                $('#student_results').hide().empty();
                return;
            }

            searchTimer = setTimeout(function () {
                $.getJSON('{{ route('visitors.search-students') }}', { q: term }, function (students) {
                    const $results = $('#student_results').empty();

                    if (students.length === 0) {
                        $results.append('<div class="list-group-item text-muted">No students found</div>').show();
                        return;
                    }

                    students.forEach(function (s) {
                        $results.append(
                            $('<button type="button" class="list-group-item list-group-item-action"></button>')
                                .text(`${s.student_uid} - ${s.name}`)
                                .on('click', function () {
                                    $('#student_search').val(`${s.student_uid} - ${s.name}`);
                                    $('#student_id').val(s.id);
                                    $results.hide();
                                })
                        );
                    });

                    $results.show();
                });
            }, 300);
        });

        // Hide the results dropdown when clicking elsewhere
        $(document).on('click', function (e) {
            if (!$(e.target).closest('#student_search, #student_results').length) {
                $('#student_results').hide();
            }
        });

        // Guard: don't let the form submit without an actual student selected
        $('form').on('submit', function (e) {
            if (!$('#student_id').val()) {
                e.preventDefault();
                alert('Please select a student from the search results.');
                $('#student_search').focus();
            }
        });
    });
</script>
@endpush
@extends('layouts.student')

@section('title', 'My Profile')
@section('page-title', 'My Profile')

@section('content')
<div class="row g-3">
    <div class="col-md-6">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h6 class="mb-3">Profile Details</h6>
                <table class="table table-sm mb-0">
                    <tr><td class="text-muted">Student UID</td><td>{{ $student->student_uid }}</td></tr>
                    <tr><td class="text-muted">Name</td><td>{{ $student->name }}</td></tr>
                    <tr><td class="text-muted">Email</td><td>{{ $student->email ?? '-' }}</td></tr>
                    <tr><td class="text-muted">Course</td><td>{{ $student->course ?? '-' }}</td></tr>
                    <tr><td class="text-muted">Department</td><td>{{ $student->department ?? '-' }}</td></tr>
                    <tr><td class="text-muted">Session</td><td>{{ $student->session ?? '-' }}</td></tr>
                    <tr><td class="text-muted">Admission Date</td><td>{{ optional($student->admission_date)->format('d M Y') ?? '-' }}</td></tr>
                </table>
                <div class="form-text mt-2">These fields are managed by the hostel office. Contact admin to update.</div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h6 class="mb-3">Update Contact Info</h6>
                <form id="profileForm">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control" value="{{ $student->phone }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-control" rows="3">{{ $student->address }}</textarea>
                    </div>
                    <div id="save-feedback" class="text-success small mb-2" style="display:none;">Saved successfully.</div>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(function () {
        $.ajaxSetup({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
        });

        // Save profile via AJAX — instant feedback, no page reload
        $('#profileForm').on('submit', function (e) {
            e.preventDefault();
            const $form = $(this);
            const $btn = $form.find('button[type=submit]').prop('disabled', true).text('Saving...');
            $('#save-feedback').hide();

            $.ajax({
                url: '{{ route('student-portal.profile.update') }}',
                method: 'POST', // Laravel reads _method=PUT from serialized form
                data: $form.serialize(),
                success: function () {
                    $('#save-feedback').fadeIn();
                    setTimeout(() => $('#save-feedback').fadeOut(), 2000);
                },
                error: function () {
                    alert('Failed to save changes.');
                },
                complete: function () {
                    $btn.prop('disabled', false).text('Save Changes');
                }
            });
        });
    });
</script>
@endpush
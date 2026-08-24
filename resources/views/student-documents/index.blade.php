@extends('layouts.app')

@section('title', 'Document Verification')
@section('page-title', 'Student Document Verification')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <form method="GET" class="d-flex gap-2">
        <input type="text" name="search" class="form-control" placeholder="Search student..." value="{{ request('search') }}">
        <select name="status" class="form-select" onchange="this.form.submit()">
            <option value="">All Status</option>
            <option value="pending" @selected(request('status') === 'pending')>Pending ({{ $pendingCount }})</option>
            <option value="approved" @selected(request('status') === 'approved')>Approved</option>
            <option value="rejected" @selected(request('status') === 'rejected')>Rejected</option>
        </select>
        <button class="btn btn-outline-secondary"><i class="bi bi-search"></i></button>
    </form>
</div>

<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr><th>Student</th><th>Type</th><th>Title</th><th>Uploaded</th><th>Status</th><th class="text-end">Actions</th></tr>
            </thead>
            <tbody id="doc-tbody">
                @forelse($documents as $doc)
                <tr data-doc-id="{{ $doc->id }}">
                    <td>
                        <a href="{{ route('students.show', $doc->student) }}">{{ $doc->student->name }}</a>
                        <div class="small text-muted">{{ $doc->student->student_uid }}</div>
                    </td>
                    <td class="text-capitalize">{{ str_replace('_', ' ', $doc->document_type) }}</td>
                    <td>{{ $doc->title }}</td>
                    <td>{{ $doc->created_at->format('d M Y') }}</td>
                    <td class="status-cell">
                        <span class="badge bg-{{ $doc->status === 'approved' ? 'success' : ($doc->status === 'rejected' ? 'danger' : 'secondary') }} text-capitalize">
                            {{ $doc->status }}
                        </span>
                    </td>
                    <td class="text-end action-cell">
                        <a href="{{ route('student-documents.download', $doc) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-download"></i></a>
                        @if($doc->status === 'pending')
                            <button class="btn btn-sm btn-outline-success review-btn" data-doc-id="{{ $doc->id }}" data-decision="approved">Approve</button>
                            <button class="btn btn-sm btn-outline-danger review-btn" data-doc-id="{{ $doc->id }}" data-decision="rejected">Reject</button>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No documents found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">{{ $documents->links() }}</div>
</div>
@endsection

@push('scripts')
<script>
    $(function () {
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

        // Approve/Reject via AJAX — row updates instantly, no reload
        $(document).on('click', '.review-btn', function () {
            const $btn = $(this);
            const docId = $btn.data('doc-id');
            const decision = $btn.data('decision');
            const $row = $(`tr[data-doc-id="${docId}"]`);

            let note = null;
            if (decision === 'rejected') {
                note = prompt('Optional: reason for rejection');
            }

            $row.find('.review-btn').prop('disabled', true);

            $.ajax({
                url: `/student-documents/${docId}/review`,
                method: 'POST',
                data: { status: decision, review_note: note },
                success: function (res) {
                    const badgeClass = res.status === 'approved' ? 'bg-success' : 'bg-danger';
                    $row.find('.status-cell').html(`<span class="badge ${badgeClass} text-capitalize">${res.status}</span>`);
                    $row.find('.review-btn').remove();
                },
                error: function () {
                    alert('Failed to update document status.');
                    $row.find('.review-btn').prop('disabled', false);
                }
            });
        });
    });
</script>
@endpush
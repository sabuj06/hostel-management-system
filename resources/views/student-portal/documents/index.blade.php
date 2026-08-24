@extends('layouts.student')

@section('title', 'My Documents')
@section('page-title', 'My Documents')

@section('content')
<div class="row g-3">
    <div class="col-md-5">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h6 class="mb-3">Upload New Document</h6>
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <form method="POST" action="{{ route('student-portal.documents.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Document Type</label>
                        <select name="document_type" class="form-select" required>
                            <option value="nid">National ID</option>
                            <option value="birth_certificate">Birth Certificate</option>
                            <option value="photo">Photo</option>
                            <option value="admission_letter">Admission Letter</option>
                            <option value="guardian_nid">Guardian's NID</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" name="title" class="form-control" placeholder="e.g. My National ID Card" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">File (PDF/JPG/PNG, max 5MB)</label>
                        <input type="file" name="file" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Upload</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-7">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h6 class="mb-3">My Uploaded Documents</h6>
                @forelse($documents as $doc)
                <div class="d-flex justify-content-between align-items-center border rounded p-2 mb-2">
                    <div>
                        <div class="fw-semibold">{{ $doc->title }}</div>
                        <div class="small text-muted text-capitalize">{{ str_replace('_', ' ', $doc->document_type) }} &middot; {{ $doc->created_at->format('d M Y') }}</div>
                        @if($doc->status === 'rejected' && $doc->review_note)
                            <div class="small text-danger">Reason: {{ $doc->review_note }}</div>
                        @endif
                    </div>
                    <div class="text-end">
                        <span class="badge bg-{{ $doc->status === 'approved' ? 'success' : ($doc->status === 'rejected' ? 'danger' : 'secondary') }} text-capitalize d-block mb-1">
                            {{ $doc->status }}
                        </span>
                        @if($doc->status !== 'approved')
                        <form action="{{ route('student-portal.documents.destroy', $doc) }}" method="POST" onsubmit="return confirm('Remove this document?')" class="d-inline">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                        @endif
                    </div>
                </div>
                @empty
                <p class="text-muted small">No documents uploaded yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
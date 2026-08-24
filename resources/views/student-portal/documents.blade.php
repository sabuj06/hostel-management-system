@extends('layouts.student')

@section('title', 'My Documents')
@section('page-title', 'My Documents')

@section('content')
@if(session('status'))
    <div class="alert alert-success">{{ session('status') }}</div>
@endif

<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <h6 class="mb-3">Upload Document</h6>
        <form method="POST" action="{{ route('student-portal.documents.upload') }}" enctype="multipart/form-data">
            @csrf
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Document Type</label>
                    <select name="document_type" class="form-select @error('document_type') is-invalid @enderror">
                        <option value="nid">NID</option>
                        <option value="birth_certificate">Birth Certificate</option>
                        <option value="photo">Photo</option>
                        <option value="other">Other</option>
                    </select>
                    @error('document_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-5">
                    <label class="form-label">File (PDF/JPG/PNG, max 5MB)</label>
                    <input type="file" name="file" class="form-control @error('file') is-invalid @enderror" accept=".pdf,.jpg,.jpeg,.png">
                    @error('file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <button class="btn btn-primary w-100">Upload</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Document Type</th>
                    <th>File</th>
                    <th>Status</th>
                    <th>Uploaded</th>
                </tr>
            </thead>
            <tbody>
                @forelse($documents as $doc)
                <tr>
                    <td class="text-capitalize">{{ str_replace('_', ' ', $doc->document_type) }}</td>
                    <td><a href="{{ $doc->url() }}" target="_blank">View</a></td>
                    <td>
                        @php
                            $badge = ['pending' => 'secondary', 'approved' => 'success', 'rejected' => 'danger'][$doc->status];
                        @endphp
                        <span class="badge bg-{{ $badge }} text-capitalize">{{ $doc->status }}</span>
                        @if($doc->status === 'rejected' && $doc->rejection_reason)
                            <div class="small text-muted">Reason: {{ $doc->rejection_reason }}</div>
                        @endif
                    </td>
                    <td>{{ $doc->created_at->format('d M Y') }}</td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center text-muted py-4">No documents uploaded yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
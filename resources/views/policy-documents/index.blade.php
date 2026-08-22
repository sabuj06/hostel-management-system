@extends('layouts.app')

@section('title', 'Policy Documents')
@section('page-title', 'Hostel Policy Documents (RAG Knowledge Base)')

@section('content')
<div class="alert alert-info small">
    <i class="bi bi-info-circle"></i> Upload your hostel rulebook, code of conduct, or any policy PDF/text file here.
    Students can then ask questions about it via "Ask About Rules" in their portal, and the assistant will answer using only these documents.
</div>

<div class="d-flex justify-content-end mb-3">
    <a href="{{ route('policy-documents.create') }}" class="btn btn-primary"><i class="bi bi-upload"></i> Upload Document</a>
</div>

<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr><th>Title</th><th>File</th><th>Sections Indexed</th><th>Status</th><th>Uploaded By</th><th class="text-end">Actions</th></tr>
            </thead>
            <tbody>
                @forelse($documents as $doc)
                <tr>
                    <td>{{ $doc->title }}</td>
                    <td class="small text-muted">{{ $doc->original_filename }}</td>
                    <td>{{ $doc->chunk_count }}</td>
                    <td>
                        <span class="badge bg-{{ $doc->status === 'ready' ? 'success' : ($doc->status === 'failed' ? 'danger' : 'secondary') }} text-capitalize">
                            {{ $doc->status }}
                        </span>
                    </td>
                    <td>{{ $doc->uploadedBy->name ?? '-' }}</td>
                    <td class="text-end">
                        <form action="{{ route('policy-documents.destroy', $doc) }}" method="POST" onsubmit="return confirm('Delete this document and all its indexed sections?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No policy documents uploaded yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">{{ $documents->links() }}</div>
</div>
@endsection
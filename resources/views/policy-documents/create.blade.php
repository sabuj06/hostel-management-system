@extends('layouts.app')

@section('title', 'Upload Policy Document')
@section('page-title', 'Upload Policy Document')

@section('content')
<div class="card shadow-sm border-0" style="max-width:600px;">
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

        <form method="POST" action="{{ route('policy-documents.store') }}" enctype="multipart/form-data" id="uploadForm">
            @csrf
            <div class="mb-3">
                <label class="form-label">Document Title</label>
                <input type="text" name="title" class="form-control" placeholder="e.g. Hostel Rulebook 2026" required>
            </div>
            <div class="mb-3">
                <label class="form-label">File (PDF or .txt, max 10MB)</label>
                <input type="file" name="file" class="form-control" accept=".pdf,.txt" required>
                <div class="form-text">PDF requires the <code>smalot/pdfparser</code> Composer package. .txt files always work.</div>
            </div>
            <button type="submit" class="btn btn-primary" id="upload-btn">Upload &amp; Index</button>
            <a href="{{ route('policy-documents.index') }}" class="btn btn-light">Cancel</a>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(function () {
        // Text extraction + chunking happens synchronously on submit, so
        // give clear feedback that it may take a few seconds for large files.
        $('#uploadForm').on('submit', function () {
            $('#upload-btn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Processing & indexing...');
        });
    });
</script>
@endpush
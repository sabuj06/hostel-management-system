@extends('layouts.student')

@section('title', 'Log Complaint')
@section('page-title', 'Log a New Complaint')

@section('content')
<div class="row g-3">
    <div class="col-md-7">
        <!-- Quick Note -> AI generates Title + Description -->
        <div class="card shadow-sm border-0 mb-3 border-start border-4 border-primary">
            <div class="card-body">
                <h6 class="mb-2"><i class="bi bi-stars text-primary"></i> Quick Note (optional)</h6>
                <p class="text-muted small mb-2">
                    Just describe the problem in your own words, however casual — the assistant will turn it into a proper title and description below.
                </p>
                <div class="d-flex gap-2">
                    <textarea id="quick_note" class="form-control" rows="2" placeholder="e.g. fan e problem hocche 3 din dhore, ghurte thake dhire dhire"></textarea>
                    <button type="button" class="btn btn-primary flex-shrink-0" id="generate-btn">
                        <i class="bi bi-magic"></i> Generate
                    </button>
                </div>
                <div id="generate-error" class="text-danger small mt-2" style="display:none;"></div>
            </div>
        </div>

        <div class="card shadow-sm border-0">
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

                <form method="POST" action="{{ route('student-portal.complaints.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" name="title" id="title" class="form-control" value="{{ old('title') }}" placeholder="e.g. Fan not working in my room" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" id="description" class="form-control" rows="5" placeholder="Describe the issue in detail..." required>{{ old('description') }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-success">Submit Complaint</button>
                    <a href="{{ route('student-portal.complaints') }}" class="btn btn-light">Cancel</a>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-5">
        <div class="card shadow-sm border-0" id="ai-box" style="display:none;">
            <div class="card-body">
                <h6 class="mb-3"><i class="bi bi-stars text-primary"></i> Auto-detected</h6>
                <div class="mb-2">
                    <div class="small text-muted">Category</div>
                    <span class="fw-semibold" id="ai-category">-</span>
                </div>
                <div>
                    <div class="small text-muted">Priority</div>
                    <span class="badge" id="ai-priority-badge">-</span>
                </div>
                <div class="form-text mt-2">This is auto-detected from your description. The hostel team will review it.</div>
            </div>
        </div>
        <div class="card shadow-sm border-0 mt-3" id="ai-placeholder">
            <div class="card-body text-center text-muted py-4">
                <i class="bi bi-stars fs-3"></i>
                <p class="small mb-0 mt-2">Start typing — we'll automatically figure out the category and urgency for you.</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(function () {
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

        let debounceTimer = null;
        const priorityColors = { low: 'bg-secondary', medium: 'bg-info text-dark', high: 'bg-warning text-dark', urgent: 'bg-danger' };

        function showSuggestion(res) {
            $('#ai-category').text(res.suggested_category);
            $('#ai-priority-badge')
                .attr('class', 'badge text-capitalize ' + (priorityColors[res.suggested_priority] || 'bg-secondary'))
                .text(res.suggested_priority);
            $('#ai-placeholder').hide();
            $('#ai-box').show();
        }

        function runAnalysis() {
            const title = $('#title').val().trim();
            const description = $('#description').val().trim();
            if (title.length < 3 && description.length < 5) {
                $('#ai-box').hide();
                $('#ai-placeholder').show();
                return;
            }
            $.ajax({
                url: '{{ route('complaints.ai-suggest') }}',
                method: 'POST',
                data: { title: title, description: description },
                success: showSuggestion
            });
        }

        $('#title, #description').on('input', function () {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(runAnalysis, 500);
        });

        // "Generate" button: turn the rough quick note into Title + Description
        $('#generate-btn').on('click', function () {
            const note = $('#quick_note').val().trim();
            const $btn = $(this);
            $('#generate-error').hide();

            if (note.length < 5) {
                $('#generate-error').text('Please write a bit more detail before generating.').show();
                return;
            }

            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Generating...');

            $.ajax({
                url: '{{ route('complaints.ai-generate') }}',
                method: 'POST',
                data: { note: note },
                success: function (res) {
                    $('#title').val(res.suggested_title);
                    $('#description').val(res.suggested_description);
                    showSuggestion(res);
                },
                error: function () {
                    $('#generate-error').text('Could not generate right now. Please fill the title/description manually.').show();
                },
                complete: function () {
                    $btn.prop('disabled', false).html('<i class="bi bi-magic"></i> Generate');
                }
            });
        });
    });
</script>
@endpush
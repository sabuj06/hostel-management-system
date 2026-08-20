@extends('layouts.app')

@section('title', 'Log Complaint')
@section('page-title', 'Log a New Complaint')

@section('content')
<div class="row g-3">
    <div class="col-md-7">
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

                <form method="POST" action="{{ route('complaints.store') }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Student</label>
                        <select name="student_id" class="form-select" required>
                            <option value="">Select Student</option>
                            @foreach($students as $s)
                                <option value="{{ $s->id }}" @selected(old('student_id') == $s->id)>{{ $s->student_uid }} - {{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" name="title" id="title" class="form-control" value="{{ old('title') }}" placeholder="e.g. Fan not working in room 204" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" id="description" class="form-control" rows="5" placeholder="Describe the issue in detail..." required>{{ old('description') }}</textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Category</label>
                            <select name="complaint_category_id" id="complaint_category_id" class="form-select">
                                <option value="">Uncategorized</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" @selected(old('complaint_category_id') == $cat->id)>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Priority</label>
                            <select name="priority" id="priority" class="form-select" required>
                                <option value="low" @selected(old('priority') === 'low')>Low</option>
                                <option value="medium" @selected(old('priority', 'medium') === 'medium')>Medium</option>
                                <option value="high" @selected(old('priority') === 'high')>High</option>
                                <option value="urgent" @selected(old('priority') === 'urgent')>Urgent</option>
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Submit Complaint</button>
                    <a href="{{ route('complaints.index') }}" class="btn btn-light">Cancel</a>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-5">
        <div class="card shadow-sm border-0" id="ai-box" style="display:none;">
            <div class="card-body">
                <h6 class="mb-3"><i class="bi bi-stars text-primary"></i> AI Suggestion</h6>

                <div class="mb-2">
                    <div class="small text-muted">Suggested Category</div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="fw-semibold" id="ai-category">-</span>
                        <span class="badge bg-light text-dark border" id="ai-confidence"></span>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="small text-muted">Suggested Priority</div>
                    <span class="badge" id="ai-priority-badge">-</span>
                    <div class="small text-muted mt-1" id="ai-reason"></div>
                </div>

                <button type="button" class="btn btn-sm btn-outline-primary" id="apply-suggestion">
                    <i class="bi bi-check2"></i> Apply Suggestion
                </button>
            </div>
        </div>

        <div class="card shadow-sm border-0 mt-3" id="ai-placeholder">
            <div class="card-body text-center text-muted py-4">
                <i class="bi bi-stars fs-3"></i>
                <p class="small mb-0 mt-2">Start typing a title and description — the assistant will suggest a category and priority automatically.</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(function () {
        let debounceTimer = null;
        let lastSuggestion = null;

        const priorityColors = { low: 'bg-secondary', medium: 'bg-info text-dark', high: 'bg-warning text-dark', urgent: 'bg-danger' };

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
                data: {
                    _token: '{{ csrf_token() }}',
                    title: title,
                    description: description
                },
                success: function (res) {
                    lastSuggestion = res;

                    $('#ai-category').text(res.suggested_category);
                    $('#ai-confidence').text(res.category_confidence > 0 ? res.category_confidence + '% match' : 'low confidence');

                    $('#ai-priority-badge')
                        .attr('class', 'badge text-capitalize ' + (priorityColors[res.suggested_priority] || 'bg-secondary'))
                        .text(res.suggested_priority);

                    $('#ai-reason').text(res.priority_reason);

                    $('#ai-placeholder').hide();
                    $('#ai-box').show();
                }
            });
        }

        // Debounced live analysis as the admin/student types
        $('#title, #description').on('input', function () {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(runAnalysis, 500);
        });

        // Apply the AI suggestion to the actual form fields
        $('#apply-suggestion').on('click', function () {
            if (!lastSuggestion) return;

            if (lastSuggestion.suggested_category_id) {
                $('#complaint_category_id').val(lastSuggestion.suggested_category_id);
            }
            $('#priority').val(lastSuggestion.suggested_priority);

            $(this).html('<i class="bi bi-check2-all"></i> Applied').prop('disabled', true);
            setTimeout(() => {
                $(this).html('<i class="bi bi-check2"></i> Apply Suggestion').prop('disabled', false);
            }, 1500);
        });
    });
</script>
@endpush
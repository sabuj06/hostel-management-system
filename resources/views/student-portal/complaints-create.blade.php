@extends('layouts.student')

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
                    <button type="submit" class="btn btn-primary">Submit Complaint</button>
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
        let debounceTimer = null;
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
                data: { _token: '{{ csrf_token() }}', title: title, description: description },
                success: function (res) {
                    $('#ai-category').text(res.suggested_category);
                    $('#ai-priority-badge')
                        .attr('class', 'badge text-capitalize ' + (priorityColors[res.suggested_priority] || 'bg-secondary'))
                        .text(res.suggested_priority);
                    $('#ai-placeholder').hide();
                    $('#ai-box').show();
                }
            });
        }

        $('#title, #description').on('input', function () {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(runAnalysis, 500);
        });
    });
</script>
@endpush
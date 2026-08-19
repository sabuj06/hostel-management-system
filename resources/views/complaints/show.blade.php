@extends('layouts.app')

@section('title', $complaint->ticket_no)
@section('page-title', 'Complaint Details')

@section('content')
<div class="row g-3">
    <div class="col-md-4">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small">{{ $complaint->ticket_no }}</span>
                    <span id="priority-badge" class="badge bg-{{ ['low' => 'secondary', 'medium' => 'info', 'high' => 'warning', 'urgent' => 'danger'][$complaint->priority] }} text-capitalize">
                        {{ $complaint->priority }}
                    </span>
                </div>
                <h6>{{ $complaint->title }}</h6>
                <p class="small">{{ $complaint->description }}</p>

                <ul class="list-unstyled small mb-0">
                    <li class="mb-2"><strong>Student:</strong> <a href="{{ route('students.show', $complaint->student) }}">{{ $complaint->student->name }}</a></li>
                    <li class="mb-2"><strong>Category:</strong> {{ $complaint->category->name ?? '-' }}</li>
                    <li class="mb-2"><strong>Room:</strong> {{ $complaint->room->room_number ?? '-' }}</li>
                    <li><strong>Logged:</strong> {{ $complaint->created_at->format('d M Y, h:i A') }}</li>
                </ul>
            </div>
        </div>

        <div class="card shadow-sm border-0 mt-3">
            <div class="card-body">
                <h6 class="mb-2">Status</h6>
                <select id="status-select" class="form-select mb-3">
                    @foreach(['open','in_progress','resolved','closed','rejected'] as $s)
                        <option value="{{ $s }}" @selected($complaint->status === $s)>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                    @endforeach
                </select>

                <h6 class="mb-2">Assign To</h6>
                <select id="assign-select" class="form-select">
                    <option value="">Unassigned</option>
                    @foreach($assignees as $user)
                        <option value="{{ $user->id }}" @selected($complaint->assigned_to === $user->id)>{{ $user->name }} ({{ $user->role->label ?? '' }})</option>
                    @endforeach
                </select>
                <div id="assign-feedback" class="small text-success mt-2" style="display:none;">Updated ✓</div>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h6 class="mb-3">Activity / Updates</h6>

                <div id="comment-list">
                    @forelse($complaint->comments as $comment)
                    <div class="border-bottom pb-2 mb-2">
                        <div class="d-flex justify-content-between">
                            <strong class="small">{{ $comment->user->name }}</strong>
                            <span class="text-muted small">{{ $comment->created_at->format('d M Y, h:i A') }}</span>
                        </div>
                        <div class="small">{{ $comment->comment }}</div>
                    </div>
                    @empty
                    <p class="text-muted small" id="no-comments">No updates yet.</p>
                    @endforelse
                </div>

                <form id="commentForm" class="mt-3">
                    @csrf
                    <div class="input-group">
                        <input type="text" name="comment" class="form-control" placeholder="Add an update or note..." required>
                        <button class="btn btn-primary" type="submit">Post</button>
                    </div>
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

        const statusBadge = {
            open: 'bg-danger', in_progress: 'bg-warning', resolved: 'bg-success',
            closed: 'bg-secondary', rejected: 'bg-dark'
        };

        // Update status via AJAX
        $('#status-select').on('change', function () {
            const status = $(this).val();
            $.ajax({
                url: '{{ route('complaints.status', $complaint) }}',
                method: 'PATCH',
                data: { status: status },
                success: function () {
                    $('#assign-feedback').stop().show().delay(1200).fadeOut();
                },
                error: function () {
                    alert('Failed to update status.');
                }
            });
        });

        // Update assignee via AJAX
        $('#assign-select').on('change', function () {
            const assignedTo = $(this).val();
            $.ajax({
                url: '{{ route('complaints.assign', $complaint) }}',
                method: 'PATCH',
                data: { assigned_to: assignedTo },
                success: function (res) {
                    $('#assign-feedback').stop().show().delay(1200).fadeOut();
                },
                error: function () {
                    alert('Failed to update assignee.');
                }
            });
        });

        // Post comment via AJAX, append instantly
        $('#commentForm').on('submit', function (e) {
            e.preventDefault();
            const $form = $(this);
            const $input = $form.find('input[name=comment]');
            const $btn = $form.find('button').prop('disabled', true);

            $.ajax({
                url: '{{ route('complaints.comments.store', $complaint) }}',
                method: 'POST',
                data: $form.serialize(),
                success: function (res) {
                    $('#no-comments').remove();
                    const c = res.comment;
                    $('#comment-list').append(`
                        <div class="border-bottom pb-2 mb-2">
                            <div class="d-flex justify-content-between">
                                <strong class="small">${c.user_name}</strong>
                                <span class="text-muted small">${c.created_at}</span>
                            </div>
                            <div class="small">${$('<div>').text(c.comment).html()}</div>
                        </div>
                    `);
                    $input.val('');
                },
                error: function () {
                    alert('Failed to post update.');
                },
                complete: function () {
                    $btn.prop('disabled', false);
                }
            });
        });
    });
</script>
@endpush
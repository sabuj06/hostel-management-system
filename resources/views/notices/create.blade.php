@extends('layouts.app')

@section('title', 'Post Notice')
@section('page-title', 'Post Notice')

@section('content')
<div class="card shadow-sm border-0 mb-3 border-start border-4 border-primary" style="max-width:750px;">
    <div class="card-body">
        <h6 class="mb-2"><i class="bi bi-stars text-primary"></i> Quick Draft (optional)</h6>
        <p class="text-muted small mb-2">Write the gist in 1-2 lines — the assistant will turn it into a polished notice below.</p>
        <div class="d-flex gap-2">
            <textarea id="quick_draft" class="form-control" rows="2" placeholder="e.g. water will be off tomorrow 10am-2pm for tank cleaning"></textarea>
            <button type="button" class="btn btn-primary flex-shrink-0" id="generate-notice-btn">
                <i class="bi bi-magic"></i> Generate
            </button>
        </div>
        <div id="generate-error" class="text-danger small mt-2" style="display:none;"></div>
    </div>
</div>

<div class="card shadow-sm border-0" style="max-width:750px;">
    <div class="card-body">
        <form method="POST" action="{{ route('notices.store') }}">
            @csrf
            @include('notices._form')
            <button class="btn btn-primary">Publish Notice</button>
            <a href="{{ route('notices.manage') }}" class="btn btn-light">Cancel</a>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(function () {
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

        // Show the Hostel dropdown only when "Specific Hostel" audience is chosen
        $('#audience').on('change', function () {
            $('#hostel-field').toggle($(this).val() === 'hostel');
        });

        // Generate a full notice from the quick draft
        $('#generate-notice-btn').on('click', function () {
            const draft = $('#quick_draft').val().trim();
            const $btn = $(this);
            $('#generate-error').hide();

            if (draft.length < 5) {
                $('#generate-error').text('Please write a bit more before generating.').show();
                return;
            }

            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Generating...');

            $.ajax({
                url: '{{ route('notices.ai-generate') }}',
                method: 'POST',
                data: { draft: draft },
                success: function (res) {
                    $('input[name=title]').val(res.title);
                    $('textarea[name=body]').val(res.body);
                },
                error: function () {
                    $('#generate-error').text('Could not generate right now. Please write the notice manually.').show();
                },
                complete: function () {
                    $btn.prop('disabled', false).html('<i class="bi bi-magic"></i> Generate');
                }
            });
        });
    });
</script>
@endpush
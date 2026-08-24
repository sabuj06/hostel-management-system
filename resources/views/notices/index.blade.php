@extends('layouts.app')

@section('title', 'Notice Board')
@section('page-title', 'Notice Board')

@section('content')
@if(auth()->user()->hasRole('admin') || auth()->user()->hasRole('warden'))
<div class="d-flex justify-content-end mb-3">
    <a href="{{ route('notices.manage') }}" class="btn btn-outline-secondary me-2"><i class="bi bi-gear"></i> Manage Notices</a>
    <a href="{{ route('notices.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Post Notice</a>
</div>
@endif

<div class="d-flex flex-column gap-3" id="notice-list">
    @forelse($notices as $notice)
    @php
        $isRead = in_array($notice->id, $readIds);
        $priorityColor = ['normal' => 'border-secondary', 'important' => 'border-warning', 'urgent' => 'border-danger'][$notice->priority];
    @endphp
    <div class="card shadow-sm border-start border-4 {{ $priorityColor }} notice-card {{ $isRead ? '' : 'bg-light' }}" data-notice-id="{{ $notice->id }}">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h6 class="mb-1 notice-title">
                        {{ $notice->title }}
                        @if($notice->priority !== 'normal')
                            <span class="badge bg-{{ $notice->priority === 'urgent' ? 'danger' : 'warning' }} text-capitalize ms-1">{{ $notice->priority }}</span>
                        @endif
                        @unless($isRead)
                            <span class="badge bg-primary read-indicator">New</span>
                        @endunless
                    </h6>
                    <div class="text-muted small mb-2">
                        {{ $notice->postedBy->name ?? 'Admin' }} &middot; {{ optional($notice->publish_date)->format('d M Y') }}
                        @if($notice->audience !== 'all')
                            &middot; <span class="text-capitalize">{{ $notice->audience }}</span> only
                        @endif
                    </div>
                    <p class="mb-0 notice-body">{{ $notice->body }}</p>

                    <div class="d-flex align-items-center gap-2 mt-2">
                        <select class="form-select form-select-sm translate-lang" style="width:auto;">
                            <option value="">Translate to...</option>
                            <option value="Bengali">বাংলা (Bengali)</option>
                            <option value="Hindi">हिन्दी (Hindi)</option>
                            <option value="English">English</option>
                            <option value="Arabic">العربية (Arabic)</option>
                        </select>
                        <button class="btn btn-sm btn-outline-secondary translate-btn" style="display:none;">
                            <i class="bi bi-translate"></i> Translate
                        </button>
                        <button class="btn btn-sm btn-link show-original-btn" style="display:none;">Show original</button>
                    </div>
                    <div class="small text-muted mt-1 translate-note" style="display:none;"></div>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="text-center text-muted py-5">No notices posted yet.</div>
    @endforelse
</div>

<div class="mt-3">{{ $notices->links() }}</div>
@endsection

@push('scripts')
<script>
    $(function () {
        $.ajaxSetup({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
        });

        // Mark a notice read the moment it scrolls into view (IntersectionObserver + jQuery AJAX)
        const observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) return;

                const $card = $(entry.target);
                if ($card.data('read-sent')) return;
                $card.data('read-sent', true);

                const noticeId = $card.data('notice-id');

                $.ajax({
                    url: `/notices/${noticeId}/read`,
                    method: 'POST',
                    success: function () {
                        $card.removeClass('bg-light');
                        $card.find('.read-indicator').fadeOut(300, function () { $(this).remove(); });
                    }
                });

                observer.unobserve(entry.target);
            });
        }, { threshold: 0.5 });

        document.querySelectorAll('.notice-card').forEach(el => observer.observe(el));

        // Translate — show the button once a language is picked, cache
        // originals so "Show original" can revert without another call
        $(document).on('change', '.translate-lang', function () {
            const $btn = $(this).closest('.notice-card').find('.translate-btn');
            $btn.toggle($(this).val() !== '');
        });

        $(document).on('click', '.translate-btn', function () {
            const $card = $(this).closest('.notice-card');
            const noticeId = $card.data('notice-id');
            const language = $card.find('.translate-lang').val();
            const $titleEl = $card.find('.notice-title');
            const $bodyEl = $card.find('.notice-body');
            const $btn = $(this);

            if (!$titleEl.data('original')) {
                $titleEl.data('original', $titleEl.contents().first().text().trim());
                $bodyEl.data('original', $bodyEl.text());
            }

            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

            $.ajax({
                url: `/notices/${noticeId}/translate`,
                method: 'POST',
                data: { language: language },
                success: function (res) {
                    $titleEl.contents().first().replaceWith(res.title + ' ');
                    $bodyEl.text(res.body);
                    $card.find('.show-original-btn').show();

                    if (!res.translated) {
                        $card.find('.translate-note').text(res.note).show();
                    } else {
                        $card.find('.translate-note').hide();
                    }
                },
                error: function () {
                    alert('Translation failed. Please try again.');
                },
                complete: function () {
                    $btn.prop('disabled', false).html('<i class="bi bi-translate"></i> Translate');
                }
            });
        });

        $(document).on('click', '.show-original-btn', function () {
            const $card = $(this).closest('.notice-card');
            const $titleEl = $card.find('.notice-title');
            const $bodyEl = $card.find('.notice-body');

            $titleEl.contents().first().replaceWith($titleEl.data('original') + ' ');
            $bodyEl.text($bodyEl.data('original'));
            $card.find('.show-original-btn').hide();
            $card.find('.translate-note').hide();
        });
    });
</script>
@endpush
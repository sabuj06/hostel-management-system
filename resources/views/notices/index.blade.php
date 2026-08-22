@extends('layouts.app')

@section('title', 'Notices')
@section('page-title', 'Notice Board')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">

    <div>
        <h4 class="mb-1">Notice Board</h4>
        <p class="text-muted mb-0">
            View all published hostel notices and announcements.
        </p>
    </div>

    @if(auth()->user()?->hasRole('admin') || auth()->user()?->hasRole('warden'))
        <div class="d-flex gap-2">

            <a href="{{ route('notices.manage') }}"
               class="btn btn-outline-secondary">
                <i class="bi bi-gear me-1"></i>
                Manage Notices
            </a>

            <a href="{{ route('notices.create') }}"
               class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>
                Create Notice
            </a>

        </div>
    @endif

</div>


{{-- Success Message --}}
@if(session('status'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle me-1"></i>
        {{ session('status') }}

        <button type="button"
                class="btn-close"
                data-bs-dismiss="alert"></button>
    </div>
@endif


{{-- Notices --}}
@if($notices->count())

    <div class="row g-4">

        @foreach($notices as $notice)

            <div class="col-md-6 col-xl-4">

                <div class="card h-100 shadow-sm border-0">

                    <div class="card-body">

                        {{-- Priority --}}
                        <div class="d-flex justify-content-between align-items-start mb-3">

                            <div>
                                @if($notice->priority === 'urgent')
                                    <span class="badge bg-danger">
                                        <i class="bi bi-exclamation-triangle me-1"></i>
                                        Urgent
                                    </span>

                                @elseif($notice->priority === 'important')
                                    <span class="badge bg-warning text-dark">
                                        <i class="bi bi-exclamation-circle me-1"></i>
                                        Important
                                    </span>

                                @else
                                    <span class="badge bg-secondary">
                                        Normal
                                    </span>
                                @endif
                            </div>

                            {{-- Read Status --}}
                            @if(in_array($notice->id, $readIds ?? []))
                                <span class="badge bg-success">
                                    <i class="bi bi-check2 me-1"></i>
                                    Read
                                </span>
                            @else
                                <span class="badge bg-light text-dark border">
                                    Unread
                                </span>
                            @endif

                        </div>


                        {{-- Title --}}
                        <h5 class="card-title fw-semibold">
                            {{ $notice->title }}
                        </h5>


                        {{-- Body --}}
                        <p class="card-text text-muted">
                            {{ \Illuminate\Support\Str::limit(strip_tags($notice->body), 180) }}
                        </p>


                        <hr>


                        {{-- Audience --}}
                        <div class="small text-muted mb-2">

                            <i class="bi bi-people me-1"></i>

                            @if($notice->audience === 'all')
                                Everyone

                            @elseif($notice->audience === 'students')
                                Students

                            @elseif($notice->audience === 'staff')
                                Staff

                            @elseif($notice->audience === 'hostel')
                                {{ $notice->hostel?->name ?? 'Specific Hostel' }}

                            @endif

                        </div>


                        {{-- Publish Date --}}
                        <div class="small text-muted mb-2">
                            <i class="bi bi-calendar-event me-1"></i>

                            Published:
                            {{ $notice->publish_date?->format('d M Y') }}
                        </div>


                        {{-- Expiry --}}
                        @if($notice->expiry_date)

                            <div class="small text-muted mb-2">
                                <i class="bi bi-calendar-x me-1"></i>

                                Expires:
                                {{ $notice->expiry_date->format('d M Y') }}
                            </div>

                        @endif


                        {{-- Posted By --}}
                        @if($notice->postedBy)

                            <div class="small text-muted">
                                <i class="bi bi-person me-1"></i>

                                Posted by:
                                {{ $notice->postedBy->name }}
                            </div>

                        @endif

                    </div>


                    {{-- Card Footer --}}
                    <div class="card-footer bg-white border-0 pt-0 pb-3 px-3">

                        <button
                            type="button"
                            class="btn btn-sm btn-outline-primary w-100"
                            onclick="markNoticeRead({{ $notice->id }}, this)"
                        >
                            <i class="bi bi-eye me-1"></i>
                            View Notice
                        </button>

                    </div>

                </div>

            </div>

        @endforeach

    </div>


    {{-- Pagination --}}
    <div class="mt-4">
        {{ $notices->links() }}
    </div>

@else

    <div class="card shadow-sm border-0">

        <div class="card-body text-center py-5">

            <i class="bi bi-megaphone fs-1 text-muted"></i>

            <h5 class="mt-3">
                No Notices Available
            </h5>

            <p class="text-muted">
                There are currently no published notices.
            </p>

            @if(auth()->user()?->hasRole('admin') || auth()->user()?->hasRole('warden'))

                <a href="{{ route('notices.create') }}"
                   class="btn btn-primary">

                    <i class="bi bi-plus-lg me-1"></i>
                    Create First Notice

                </a>

            @endif

        </div>

    </div>

@endif

@endsection


@push('scripts')

<script>

function markNoticeRead(noticeId, button) {

    fetch(`/notices/${noticeId}/read`, {

        method: 'POST',

        headers: {
            'X-CSRF-TOKEN': document
                .querySelector('meta[name="csrf-token"]')
                .getAttribute('content'),

            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }

    })
    .then(response => response.json())

    .then(data => {

        if (data.success) {

            button.innerHTML =
                '<i class="bi bi-check-circle me-1"></i> Read';

            button.classList.remove('btn-outline-primary');

            button.classList.add('btn-success');

            button.disabled = true;

        }

    })

    .catch(error => {
        console.error('Notice read error:', error);
    });

}

</script>

@endpush
@extends('layouts.app')

@section('title', 'Create Notice')
@section('page-title', 'Create Notice')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Create Notice</h4>
        <p class="text-muted mb-0">
            Create a new announcement for hostel users.
        </p>
    </div>

    <a href="{{ route('notices.manage') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>
        Back to Notices
    </a>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">

        <form action="{{ route('notices.store') }}" method="POST">
            @csrf

            {{-- Title --}}
            <div class="mb-3">
                <label for="title" class="form-label fw-semibold">
                    Notice Title
                </label>

                <input
                    type="text"
                    name="title"
                    id="title"
                    class="form-control @error('title') is-invalid @enderror"
                    value="{{ old('title') }}"
                    placeholder="Enter notice title"
                    required
                >

                @error('title')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>


            {{-- Body --}}
            <div class="mb-3">
                <label for="body" class="form-label fw-semibold">
                    Notice Details
                </label>

                <textarea
                    name="body"
                    id="body"
                    rows="6"
                    class="form-control @error('body') is-invalid @enderror"
                    placeholder="Write the notice details here..."
                    required
                >{{ old('body') }}</textarea>

                @error('body')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>


            <div class="row">

                {{-- Audience --}}
                <div class="col-md-6 mb-3">
                    <label for="audience" class="form-label fw-semibold">
                        Audience
                    </label>

                    <select
                        name="audience"
                        id="audience"
                        class="form-select @error('audience') is-invalid @enderror"
                        required
                    >
                        <option value="">Select Audience</option>

                        <option value="all"
                            {{ old('audience') === 'all' ? 'selected' : '' }}>
                            Everyone
                        </option>

                        <option value="students"
                            {{ old('audience') === 'students' ? 'selected' : '' }}>
                            Students
                        </option>

                        <option value="staff"
                            {{ old('audience') === 'staff' ? 'selected' : '' }}>
                            Staff
                        </option>

                        <option value="hostel"
                            {{ old('audience') === 'hostel' ? 'selected' : '' }}>
                            Specific Hostel
                        </option>
                    </select>

                    @error('audience')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>


                {{-- Priority --}}
                <div class="col-md-6 mb-3">
                    <label for="priority" class="form-label fw-semibold">
                        Priority
                    </label>

                    <select
                        name="priority"
                        id="priority"
                        class="form-select @error('priority') is-invalid @enderror"
                        required
                    >
                        <option value="">Select Priority</option>

                        <option value="normal"
                            {{ old('priority', 'normal') === 'normal' ? 'selected' : '' }}>
                            Normal
                        </option>

                        <option value="important"
                            {{ old('priority') === 'important' ? 'selected' : '' }}>
                            Important
                        </option>

                        <option value="urgent"
                            {{ old('priority') === 'urgent' ? 'selected' : '' }}>
                            Urgent
                        </option>
                    </select>

                    @error('priority')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

            </div>


            {{-- Hostel --}}
            <div class="mb-3" id="hostel-wrapper">
                <label for="hostel_id" class="form-label fw-semibold">
                    Hostel
                </label>

                <select
                    name="hostel_id"
                    id="hostel_id"
                    class="form-select @error('hostel_id') is-invalid @enderror"
                >
                    <option value="">Select Hostel</option>

                    @foreach($hostels as $hostel)
                        <option
                            value="{{ $hostel->id }}"
                            {{ old('hostel_id') == $hostel->id ? 'selected' : '' }}
                        >
                            {{ $hostel->name }}
                        </option>
                    @endforeach
                </select>

                <div class="form-text">
                    Required only when the audience is "Specific Hostel".
                </div>

                @error('hostel_id')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>


            <div class="row">

                {{-- Publish Date --}}
                <div class="col-md-6 mb-3">
                    <label for="publish_date" class="form-label fw-semibold">
                        Publish Date
                    </label>

                    <input
                        type="date"
                        name="publish_date"
                        id="publish_date"
                        class="form-control @error('publish_date') is-invalid @enderror"
                        value="{{ old('publish_date', now()->format('Y-m-d')) }}"
                    >

                    @error('publish_date')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>


                {{-- Expiry Date --}}
                <div class="col-md-6 mb-3">
                    <label for="expiry_date" class="form-label fw-semibold">
                        Expiry Date
                    </label>

                    <input
                        type="date"
                        name="expiry_date"
                        id="expiry_date"
                        class="form-control @error('expiry_date') is-invalid @enderror"
                        value="{{ old('expiry_date') }}"
                    >

                    <div class="form-text">
                        Optional. Leave blank if the notice should not expire.
                    </div>

                    @error('expiry_date')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

            </div>


            {{-- Status --}}
            <div class="mb-4">
                <label for="status" class="form-label fw-semibold">
                    Status
                </label>

                <select
                    name="status"
                    id="status"
                    class="form-select @error('status') is-invalid @enderror"
                    required
                >
                    <option value="draft"
                        {{ old('status') === 'draft' ? 'selected' : '' }}>
                        Draft
                    </option>

                    <option value="published"
                        {{ old('status', 'published') === 'published' ? 'selected' : '' }}>
                        Published
                    </option>

                    <option value="archived"
                        {{ old('status') === 'archived' ? 'selected' : '' }}>
                        Archived
                    </option>
                </select>

                @error('status')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            {{-- Phase 14 — Email/SMS Notification --}}
@unless(isset($notice))
<div class="form-check mb-4">
    <input
        type="checkbox"
        class="form-check-input"
        name="notify_now"
        id="notify_now"
        value="1"
        checked
    >

    <label class="form-check-label" for="notify_now">
        Notify students immediately via Email/SMS when published
    </label>
</div>
@endunless


            {{-- Buttons --}}
            <div class="d-flex justify-content-end gap-2">

                <a
                    href="{{ route('notices.manage') }}"
                    class="btn btn-light border"
                >
                    Cancel
                </a>

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-send me-1"></i>
                    Create Notice
                </button>

            </div>

        </form>

    </div>
</div>

@endsection


@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const audience = document.getElementById('audience');
    const hostelWrapper = document.getElementById('hostel-wrapper');
    const hostel = document.getElementById('hostel_id');

    function toggleHostel() {

        if (audience.value === 'hostel') {

            hostelWrapper.style.display = 'block';
            hostel.required = true;

        } else {

            hostelWrapper.style.display = 'none';
            hostel.required = false;
            hostel.value = '';

        }
    }

    audience.addEventListener('change', toggleHostel);

    toggleHostel();
});
</script>
@endpush
@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')

{{-- =========================================================
     WELCOME
========================================================= --}}

<div class="mb-4">

    <h4 class="fw-bold mb-1">
        Welcome back, {{ auth()->user()->name }} 👋
    </h4>

    <div class="text-muted">
        Here's what's happening in your hostel today.
    </div>

</div>


{{-- =========================================================
     BASIC STATISTICS
========================================================= --}}

<div class="row g-3 mb-4">

    {{-- Total Users --}}
    <div class="col-xl-3 col-md-6">

        <div class="card border-0 shadow-sm h-100">

            <div class="card-body">

                <div class="d-flex justify-content-between">

                    <div>

                        <div class="text-muted small">
                            Total Users
                        </div>

                        <div class="fs-3 fw-bold">
                            {{ $stats['total_users'] ?? 0 }}
                        </div>

                    </div>

                    <div class="text-primary fs-2">
                        <i class="bi bi-people"></i>
                    </div>

                </div>

                <div class="small text-muted mt-2">
                    All registered users
                </div>

            </div>

        </div>

    </div>


    {{-- Active Users --}}
    <div class="col-xl-3 col-md-6">

        <div class="card border-0 shadow-sm h-100">

            <div class="card-body">

                <div class="d-flex justify-content-between">

                    <div>

                        <div class="text-muted small">
                            Active Users
                        </div>

                        <div class="fs-3 fw-bold text-success">
                            {{ $stats['active_users'] ?? 0 }}
                        </div>

                    </div>

                    <div class="text-success fs-2">
                        <i class="bi bi-person-check"></i>
                    </div>

                </div>

                <div class="small text-muted mt-2">
                    Currently active accounts
                </div>

            </div>

        </div>

    </div>


    {{-- Students --}}
    <div class="col-xl-3 col-md-6">

        <div class="card border-0 shadow-sm h-100">

            <div class="card-body">

                <div class="d-flex justify-content-between">

                    <div>

                        <div class="text-muted small">
                            Total Students
                        </div>

                        <div class="fs-3 fw-bold text-info">
                            {{ $stats['total_students'] ?? 0 }}
                        </div>

                    </div>

                    <div class="text-info fs-2">
                        <i class="bi bi-mortarboard"></i>
                    </div>

                </div>

                <div class="small text-muted mt-2">
                    Registered hostel students
                </div>

            </div>

        </div>

    </div>


    {{-- Role --}}
    <div class="col-xl-3 col-md-6">

        <div class="card border-0 shadow-sm h-100">

            <div class="card-body">

                <div class="d-flex justify-content-between">

                    <div>

                        <div class="text-muted small">
                            Your Role
                        </div>

                        <div class="fs-5 fw-bold text-primary">
                            {{ ucfirst($stats['role'] ?? 'User') }}
                        </div>

                    </div>

                    <div class="text-primary fs-2">
                        <i class="bi bi-shield-check"></i>
                    </div>

                </div>

                <div class="small text-muted mt-2">
                    Your current access level
                </div>

            </div>

        </div>

    </div>

</div>



{{-- =========================================================
     SECOND ROW
========================================================= --}}

<div class="row g-3 mb-4">


    {{-- Complaints --}}
    <div class="col-lg-4">

        <div class="card border-0 shadow-sm">

            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center">

                    <div>

                        <div class="text-muted small">
                            Open Complaints
                        </div>

                        <div class="fs-3 fw-bold">
                            {{ $stats['open_complaints'] ?? 0 }}
                        </div>

                    </div>

                    <i class="bi bi-exclamation-triangle fs-1 text-warning"></i>

                </div>

                <a
                    href="{{ route('complaints.index') }}"
                    class="btn btn-sm btn-outline-primary mt-3"
                >
                    View Complaints
                </a>

            </div>

        </div>

    </div>



    {{-- Leave Requests --}}
    <div class="col-lg-4">

        <div class="card border-0 shadow-sm">

            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center">

                    <div>

                        <div class="text-muted small">
                            Pending Leave Requests
                        </div>

                        <div class="fs-3 fw-bold">
                            {{ $stats['pending_leaves'] ?? 0 }}
                        </div>

                    </div>

                    <i class="bi bi-envelope-paper fs-1 text-info"></i>

                </div>

                <a
                    href="{{ route('leave-requests.index') }}"
                    class="btn btn-sm btn-outline-primary mt-3"
                >
                    View Requests
                </a>

            </div>

        </div>

    </div>



    {{-- Occupancy --}}
    <div class="col-lg-4">

        <div class="card border-0 shadow-sm">

            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center">

                    <div>

                        <div class="text-muted small">
                            Hostel Occupancy
                        </div>

                        <div class="fs-3 fw-bold">
                            {{ $stats['occupancy'] ?? 0 }}%
                        </div>

                    </div>

                    <i class="bi bi-house-check fs-1 text-success"></i>

                </div>


                <div class="progress mt-3" style="height:8px;">

                    <div
                        class="progress-bar"
                        style="width: {{ min($stats['occupancy'] ?? 0, 100) }}%;"
                    ></div>

                </div>

            </div>

        </div>

    </div>

</div>



{{-- =========================================================
     QUICK ACTIONS
========================================================= --}}

<div class="card border-0 shadow-sm mb-4">

    <div class="card-body">

        <h5 class="fw-bold mb-3">
            <i class="bi bi-lightning-charge me-2"></i>
            Quick Actions
        </h5>


        <div class="d-flex flex-wrap gap-2">


            @if(
                auth()->user()?->hasRole('admin') ||
                auth()->user()?->hasRole('warden')
            )

                <a
                    href="{{ route('students.create') }}"
                    class="btn btn-primary"
                >
                    <i class="bi bi-person-plus me-1"></i>
                    Add Student
                </a>


                <a
                    href="{{ route('room-allocations.create') }}"
                    class="btn btn-outline-primary"
                >
                    <i class="bi bi-house-add me-1"></i>
                    Allocate Room
                </a>


                

            @endif


            @if(
                auth()->user()?->hasRole('admin') ||
                auth()->user()?->hasRole('warden') ||
                auth()->user()?->hasRole('staff')
            )

                <a
                    href="{{ route('visitors.create') }}"
                    class="btn btn-outline-secondary"
                >
                    <i class="bi bi-person-plus me-1"></i>
                    Add Visitor
                </a>

            @endif


            @if(auth()->user()?->hasRole('admin'))

                <a
                    href="{{ route('notices.create') }}"
                    class="btn btn-outline-warning"
                >
                    <i class="bi bi-megaphone me-1"></i>
                    Create Notice
                </a>

            @endif

        </div>

    </div>

</div>



{{-- =========================================================
     RECENT ACTIVITY + NOTICES
========================================================= --}}

<div class="row g-3">


    {{-- Recent Activity --}}
    <div class="col-lg-7">

        <div class="card border-0 shadow-sm h-100">

            <div class="card-header bg-white border-0">

                <div class="d-flex justify-content-between">

                    <h5 class="fw-bold mb-0">
                        <i class="bi bi-clock-history me-2"></i>
                        Recent Activity
                    </h5>


                    @if(
                        auth()->user()?->hasRole('admin') ||
                        auth()->user()?->hasRole('warden')
                    )

                        <a
                            href="{{ route('activity-logs.index') }}"
                            class="small text-decoration-none"
                        >
                            View All
                        </a>

                    @endif

                </div>

            </div>


            <div class="card-body p-0">

                @if(isset($recentActivities) && $recentActivities->count())

                    <div class="list-group list-group-flush">

                        @foreach($recentActivities as $activity)

                            <div class="list-group-item px-3 py-3">

                                <div class="d-flex">

                                    <div class="me-3">

                                        <i class="bi bi-activity fs-5"></i>

                                    </div>


                                    <div class="flex-grow-1">

                                        <div class="fw-semibold">
                                            {{ $activity->description }}
                                        </div>

                                        <div class="small text-muted">

                                            {{ $activity->user->name ?? 'System' }}

                                            •

                                            {{ $activity->created_at?->diffForHumans() }}

                                        </div>

                                    </div>

                                </div>

                            </div>

                        @endforeach

                    </div>

                @else

                    <div class="p-4 text-center text-muted">

                        <i class="bi bi-clock fs-2 d-block mb-2"></i>

                        No recent activity found.

                    </div>

                @endif

            </div>

        </div>

    </div>



    {{-- Recent Notices --}}
    <div class="col-lg-5">

        <div class="card border-0 shadow-sm h-100">

            <div class="card-header bg-white border-0">

                <h5 class="fw-bold mb-0">

                    <i class="bi bi-megaphone me-2"></i>

                    Recent Notices

                </h5>

            </div>


            <div class="card-body p-0">

                @if(isset($recentNotices) && $recentNotices->count())

                    <div class="list-group list-group-flush">

                        @foreach($recentNotices as $notice)

                            <a
                                href="{{ route('notices.index') }}"
                                class="list-group-item list-group-item-action px-3 py-3"
                            >

                                <div class="fw-semibold">

                                    {{ $notice->title }}

                                </div>

                                <div class="small text-muted">

                                    {{ $notice->created_at?->diffForHumans() }}

                                </div>

                            </a>

                        @endforeach

                    </div>

                @else

                    <div class="p-4 text-center text-muted">

                        <i class="bi bi-megaphone fs-2 d-block mb-2"></i>

                        No recent notices.

                    </div>

                @endif

            </div>

        </div>

    </div>

</div>

@endsection
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', 'Dashboard') - Hostel Management</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <style>
        body {
            background: #f4f6f9;
        }

        .sidebar {
            min-height: 100vh;
            background: #1e2a3a;
        }

        .sidebar a {
            color: #c9d3de;
        }

        .sidebar a.active,
        .sidebar a:hover {
            color: #fff;
            background: #2c3e50;
        }

        .navbar-brand {
            font-weight: 600;
        }

        .sidebar-section {
            color: #7f8c9a;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .08em;
            margin-top: 18px;
            margin-bottom: 6px;
            padding-left: 8px;
        }
    </style>

    @stack('styles')
</head>

<body>

<div class="d-flex">

    {{-- =========================================================
         SIDEBAR
    ========================================================== --}}

    <nav class="sidebar p-3" style="width:240px;">

        {{-- Logo --}}
        <div class="text-white fs-5 fw-bold mb-4">
            <i class="bi bi-building"></i>
            Hostel MS
        </div>


        <ul class="nav nav-pills flex-column gap-1">

            {{-- =================================================
                 DASHBOARD
            ================================================== --}}

            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                   href="{{ route('dashboard') }}">

                    <i class="bi bi-speedometer2 me-2"></i>
                    Dashboard

                </a>
            </li>


            {{-- =================================================
                 MANAGEMENT
            ================================================== --}}

            @if(auth()->user()?->hasRole('admin') ||
                auth()->user()?->hasRole('warden'))

                <li class="sidebar-section">
                    MANAGEMENT
                </li>


                {{-- Users --}}
                @if(auth()->user()?->hasRole('admin'))

                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}"
                           href="{{ route('users.index') }}">

                            <i class="bi bi-people me-2"></i>
                            Users

                        </a>
                    </li>


                    {{-- Roles --}}
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('roles.*') ? 'active' : '' }}"
                           href="{{ route('roles.index') }}">

                            <i class="bi bi-shield-lock me-2"></i>
                            Roles & Permissions

                        </a>
                    </li>

                @endif


                {{-- Students --}}
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('students.*') ? 'active' : '' }}"
                       href="{{ route('students.index') }}">

                        <i class="bi bi-person-badge me-2"></i>
                        Students

                    </a>
                </li>


                


                {{-- Hostels --}}
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('hostels.*') ? 'active' : '' }}"
                       href="{{ route('hostels.index') }}">

                        <i class="bi bi-building me-2"></i>
                        Hostels

                    </a>
                </li>


                {{-- Blocks --}}
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('blocks.*') ? 'active' : '' }}"
                       href="{{ route('blocks.index') }}">

                        <i class="bi bi-grid-1x2 me-2"></i>
                        Blocks

                    </a>
                </li>


                {{-- Floors --}}
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('floors.*') ? 'active' : '' }}"
                       href="{{ route('floors.index') }}">

                        <i class="bi bi-layers me-2"></i>
                        Floors

                    </a>
                </li>


                {{-- Rooms --}}
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('rooms.*') ? 'active' : '' }}"
                       href="{{ route('rooms.index') }}">

                        <i class="bi bi-door-open me-2"></i>
                        Rooms

                    </a>
                </li>


                {{-- Beds --}}
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('beds.*') ? 'active' : '' }}"
                       href="{{ route('beds.index') }}">

                        <i class="bi bi-grid-3x3-gap me-2"></i>
                        Beds

                    </a>
                </li>


                {{-- Room Allocation --}}
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('room-allocations.*') ? 'active' : '' }}"
                       href="{{ route('room-allocations.index') }}">

                        <i class="bi bi-arrow-left-right me-2"></i>
                        Room Allocation

                    </a>
                </li>


                {{-- Assets / Inventory --}}
<li class="nav-item">
    <a class="nav-link {{ request()->routeIs('assets.*') ? 'active' : '' }}"
       href="{{ route('assets.index') }}">

        <i class="bi bi-box-seam me-2"></i>
        Assets / Inventory

    </a>
</li>


{{-- Asset Damage Reports --}}
<li class="nav-item">
    <a class="nav-link {{ request()->routeIs('asset-damage-reports.*') ? 'active' : '' }}"
       href="{{ route('asset-damage-reports.index') }}">

        <i class="bi bi-tools me-2"></i>
        Damage Reports

    </a>
</li>

            @endif


            {{-- =================================================
                 FINANCE
            ================================================== --}}

            @if(auth()->user()?->hasRole('admin') ||
                auth()->user()?->hasRole('warden'))

                <li class="sidebar-section">
                    FINANCE
                </li>


                {{-- Fee Structures --}}
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('fee-structures.*') ? 'active' : '' }}"
                       href="{{ route('fee-structures.index') }}">

                        <i class="bi bi-cash-coin me-2"></i>
                        Fee Structures

                    </a>
                </li>


                {{-- Invoices --}}
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('invoices.*') ? 'active' : '' }}"
                       href="{{ route('invoices.index') }}">

                        <i class="bi bi-receipt me-2"></i>
                        Invoices

                    </a>
                </li>


                


                {{-- Mess Bills --}}
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('mess-bills.*') ? 'active' : '' }}"
                       href="{{ route('mess-bills.create') }}">

                        <i class="bi bi-receipt-cutoff me-2"></i>
                        Mess Bills

                    </a>
                </li>


                {{-- Mess Cuts --}}
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('mess-cuts.*') ? 'active' : '' }}"
                       href="{{ route('mess-cuts.index') }}">

                        <i class="bi bi-scissors me-2"></i>
                        Mess Cuts

                    </a>
                </li>

            @endif


            {{-- =================================================
                 DAILY OPERATIONS
            ================================================== --}}

            @if(auth()->user()?->hasRole('admin') ||
                auth()->user()?->hasRole('warden') ||
                auth()->user()?->hasRole('staff'))

                <li class="sidebar-section">
                    DAILY OPERATIONS
                </li>


                {{-- Complaints --}}
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('complaints.*') ? 'active' : '' }}"
                       href="{{ route('complaints.index') }}">

                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Complaints

                    </a>
                </li>


                {{-- Visitors --}}
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('visitors.*') ? 'active' : '' }}"
                       href="{{ route('visitors.index') }}">

                        <i class="bi bi-person-walking me-2"></i>
                        Visitors

                    </a>
                </li>


                {{-- Attendance --}}
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('attendance.*') ? 'active' : '' }}"
                       href="{{ route('attendance.index') }}">

                        <i class="bi bi-calendar-check me-2"></i>
                        Attendance

                    </a>
                </li>


                {{-- Leave Requests --}}
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('leave-requests.*') ? 'active' : '' }}"
                       href="{{ route('leave-requests.index') }}">

                        <i class="bi bi-calendar2-check me-2"></i>
                        Leave Requests

                    </a>
                </li>

            @endif


            {{-- =================================================
                 NOTICES
            ================================================== --}}

            @if(auth()->user())

                <li class="sidebar-section">
                    COMMUNICATION
                </li>

                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('notices.*') ? 'active' : '' }}"
                       href="{{ route('notices.index') }}">

                        <i class="bi bi-megaphone me-2"></i>
                        Notices

                    </a>
                </li>

            @endif


            {{-- =================================================
                 MEAL MANAGEMENT
            ================================================== --}}

            @if(auth()->user()?->hasRole('admin') ||
                auth()->user()?->hasRole('warden'))

                <li class="sidebar-section">
                    MEAL MANAGEMENT
                </li>


                {{-- Meal Menu --}}
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('meal-menu.*') ? 'active' : '' }}"
                       href="{{ route('meal-menu.index') }}">

                        <i class="bi bi-egg-fried me-2"></i>
                        Meal Menu

                    </a>
                </li>


                {{-- Mess Cuts --}}
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('mess-cuts.*') ? 'active' : '' }}"
                       href="{{ route('mess-cuts.index') }}">

                        <i class="bi bi-scissors me-2"></i>
                        Mess Cuts

                    </a>
                </li>

            @endif


            {{-- =================================================
                 REPORTS
            ================================================== --}}

            @if(auth()->user()?->hasRole('admin') ||
                auth()->user()?->hasRole('warden'))

                <li class="sidebar-section">
                    REPORTS
                </li>

                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}"
                       href="{{ route('reports.index') }}">

                        <i class="bi bi-bar-chart-line me-2"></i>
                        Reports & Analytics

                    </a>
                </li>

            @endif


            {{-- =================================================
                 STUDENT PORTAL
            ================================================== --}}

            @if(auth()->user()?->hasRole('student'))

                <li class="sidebar-section">
                    MY HOSTEL
                </li>


                {{-- Profile --}}
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('student-portal.profile') ? 'active' : '' }}"
                       href="{{ route('student-portal.profile') }}">

                        <i class="bi bi-person-badge me-2"></i>
                        My Profile

                    </a>
                </li>


                {{-- Invoices --}}
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('student-portal.invoices') ? 'active' : '' }}"
                       href="{{ route('student-portal.invoices') }}">

                        <i class="bi bi-receipt me-2"></i>
                        My Invoices

                    </a>
                </li>


                {{-- Complaints --}}
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('student-portal.complaints*') ? 'active' : '' }}"
                       href="{{ route('student-portal.complaints') }}">

                        <i class="bi bi-exclamation-triangle me-2"></i>
                        My Complaints

                    </a>
                </li>


                {{-- Leave Requests --}}
<li class="nav-item">
    <a class="nav-link {{ request()->routeIs('student-portal.leave-requests*') ? 'active' : '' }}"
       href="{{ route('student-portal.leave-requests') }}">

        <i class="bi bi-calendar2-check me-2"></i>
        My Leave Requests

    </a>
</li>

{{-- Meal Menu --}}
<li class="nav-item">
    <a class="nav-link {{ request()->routeIs('student-portal.meal-menu') ? 'active' : '' }}"
       href="{{ route('student-portal.meal-menu') }}">

        <i class="bi bi-egg-fried me-2"></i>
        Meal Menu

    </a>
</li>


                {{-- Notices --}}
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('notices.*') ? 'active' : '' }}"
                       href="{{ route('notices.index') }}">

                        <i class="bi bi-megaphone me-2"></i>
                        Notices

                    </a>
                </li>

            @endif

        </ul>

    </nav>


    {{-- =========================================================
         MAIN CONTENT
    ========================================================== --}}

    <div class="flex-grow-1">

        {{-- Top Navbar --}}
        <nav class="navbar navbar-light bg-white shadow-sm px-3">

            <span class="navbar-text fw-semibold">
                @yield('page-title', 'Dashboard')
            </span>

            <div class="dropdown">

                <button class="btn btn-light dropdown-toggle"
                        data-bs-toggle="dropdown">

                    <i class="bi bi-person-circle"></i>
                    {{ auth()->user()->name ?? '' }}

                </button>

                <ul class="dropdown-menu dropdown-menu-end">

                    <li>
                        <form action="{{ route('logout') }}" method="POST">

                            @csrf

                            <button class="dropdown-item" type="submit">
                                <i class="bi bi-box-arrow-right me-2"></i>
                                Logout
                            </button>

                        </form>
                    </li>

                </ul>

            </div>

        </nav>


        {{-- Page Content --}}
        <div class="p-4">

            @if(session('status'))

                <div class="alert alert-success alert-dismissible fade show">

                    {{ session('status') }}

                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="alert">
                    </button>

                </div>

            @endif

            @yield('content')

        </div>

    </div>

</div>


{{-- =============================================================
     JAVASCRIPT
============================================================= --}}

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>

@stack('scripts')

</body>
</html>
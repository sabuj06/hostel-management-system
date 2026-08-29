<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', 'Dashboard') - Hostel Management</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <style>
        body {
            background: #f4f6f9;
            margin: 0;
        }

        .sidebar {
            min-height: 100vh;
            width: 250px;
            flex-shrink: 0;
            background: #17212b;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.08);
        }

        .sidebar-logo {
            color: #ffffff;
            font-size: 20px;
            font-weight: 700;
            padding: 8px 10px;
        }

        .sidebar .menu-title {
            color: #718096;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            padding: 16px 12px 7px;
        }

        .sidebar a {
            text-decoration: none;
        }

        .sidebar .nav-link {
            color: #b8c4d0;
            border-radius: 8px;
            padding: 9px 12px;
            transition: all 0.2s ease;
        }

        .sidebar .nav-link:hover {
            color: #ffffff;
            background: #22303d;
        }

        .sidebar .nav-link.active {
            color: #ffffff;
            background: #2563eb;
            font-weight: 600;
            box-shadow: 0 3px 8px rgba(37, 99, 235, 0.25);
        }

        .sidebar .nav-link i {
            min-width: 18px;
        }

        /* Dropdown parent button */
        .sidebar .dropdown-toggle {
            width: 100%;
            border: 1px solid transparent;
            background: transparent;
            color: #c5d0db;
            text-align: left;
            padding: 10px 12px;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .sidebar .dropdown-toggle:hover {
            color: #ffffff;
            background: #22303d;
        }

        .sidebar .dropdown-toggle:focus {
            box-shadow: none;
            outline: none;
        }

        .sidebar .dropdown-toggle[aria-expanded="true"] {
            background: #263747;
            color: #ffffff;
            border-left: 3px solid #3b82f6;
            padding-left: 9px;
            font-weight: 600;
        }

        .sidebar .dropdown-toggle::after {
            float: right;
            margin-top: 7px;
            transition: transform 0.2s ease;
        }

        .sidebar .dropdown-toggle[aria-expanded="true"]::after {
            transform: rotate(180deg);
        }

        /* Submenu */
        .sidebar .submenu {
            margin: 4px 0 5px 8px;
            padding: 5px 0 5px 10px;
            border-left: 1px solid #344555;
        }

        .sidebar .submenu .nav-link {
            font-size: 14px;
            padding: 8px 12px;
            margin: 2px 0;
            border-radius: 6px;
        }

        .sidebar .submenu .nav-link:hover {
            background: #22303d;
            color: #ffffff;
            transform: translateX(2px);
        }

        .sidebar .submenu .nav-link.active {
            color: #ffffff;
            background: #2563eb;
            font-weight: 600;
        }

        .sidebar .collapse {
            transition: height 0.25s ease;
        }

        .content-wrapper {
            min-height: 100vh;
            min-width: 0;
        }

        .navbar-brand {
            font-weight: 600;
        }

        /* Student sidebar small icon spacing */
        .student-menu .nav-link {
            margin-bottom: 2px;
        }

    </style>

    @stack('styles')
</head>

<body>

<div class="d-flex">

    {{-- =========================================================
         SIDEBAR
    ========================================================== --}}

    <nav class="sidebar p-3">

        {{-- Logo --}}
        <div class="sidebar-logo mb-4">
            <i class="bi bi-building me-2"></i>
            Hostel MS
        </div>


        <ul class="nav nav-pills flex-column gap-1">


            {{-- =====================================================
                 DASHBOARD
            ====================================================== --}}

            <li class="nav-item">

                <a
                    href="{{ route('dashboard') }}"
                    class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                >
                    <i class="bi bi-speedometer2 me-2"></i>
                    Dashboard
                </a>

            </li>


            {{-- =====================================================
                 HOSTEL MANAGEMENT
            ====================================================== --}}

            @if(auth()->user()?->hasRole('admin') ||
                auth()->user()?->hasRole('warden'))

                <li class="menu-title">
                    Hostel Management
                </li>

                @php
                    $hostelOpen =
                        request()->routeIs('hostels.*') ||
                        request()->routeIs('blocks.*') ||
                        request()->routeIs('floors.*') ||
                        request()->routeIs('rooms.*') ||
                        request()->routeIs('beds.*');
                @endphp

                <li class="nav-item">

                    <button
                        class="dropdown-toggle"
                        type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#hostelMenu"
                        aria-expanded="{{ $hostelOpen ? 'true' : 'false' }}"
                    >
                        <i class="bi bi-building me-2"></i>
                        Hostel Structure
                    </button>

                    <div
                        id="hostelMenu"
                        class="collapse submenu {{ $hostelOpen ? 'show' : '' }}"
                    >

                        <a
                            href="{{ route('hostels.index') }}"
                            class="nav-link {{ request()->routeIs('hostels.*') ? 'active' : '' }}"
                        >
                            <i class="bi bi-building me-2"></i>
                            Hostels
                        </a>

                        <a
                            href="{{ route('blocks.index') }}"
                            class="nav-link {{ request()->routeIs('blocks.*') ? 'active' : '' }}"
                        >
                            <i class="bi bi-grid-1x2 me-2"></i>
                            Blocks
                        </a>

                        <a
                            href="{{ route('floors.index') }}"
                            class="nav-link {{ request()->routeIs('floors.*') ? 'active' : '' }}"
                        >
                            <i class="bi bi-layers me-2"></i>
                            Floors
                        </a>

                        <a
                            href="{{ route('rooms.index') }}"
                            class="nav-link {{ request()->routeIs('rooms.*') ? 'active' : '' }}"
                        >
                            <i class="bi bi-door-open me-2"></i>
                            Rooms
                        </a>

                        <a
                            href="{{ route('beds.index') }}"
                            class="nav-link {{ request()->routeIs('beds.*') ? 'active' : '' }}"
                        >
                            <i class="bi bi-grid-3x3-gap me-2"></i>
                            Beds
                        </a>

                    </div>

                </li>

            @endif


            {{-- =====================================================
                 STUDENTS
            ====================================================== --}}

            @if(auth()->user()?->hasRole('admin') ||
                auth()->user()?->hasRole('warden'))

                @php
                    $studentOpen =
                        request()->routeIs('students.*') ||
                        request()->routeIs('room-allocations.*') ||
                        request()->routeIs('student-documents.*');
                @endphp

                <li class="nav-item">

                    <button
                        class="dropdown-toggle"
                        type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#studentMenu"
                        aria-expanded="{{ $studentOpen ? 'true' : 'false' }}"
                    >
                        <i class="bi bi-people me-2"></i>
                        Students
                    </button>

                    <div
                        id="studentMenu"
                        class="collapse submenu {{ $studentOpen ? 'show' : '' }}"
                    >

                        <a
                            href="{{ route('students.index') }}"
                            class="nav-link {{ request()->routeIs('students.*') ? 'active' : '' }}"
                        >
                            <i class="bi bi-person-badge me-2"></i>
                            Students
                        </a>

                        <a
                            href="{{ route('room-allocations.index') }}"
                            class="nav-link {{ request()->routeIs('room-allocations.*') ? 'active' : '' }}"
                        >
                            <i class="bi bi-arrow-left-right me-2"></i>
                            Room Allocation
                        </a>

                        <a
                            href="{{ route('student-documents.index') }}"
                            class="nav-link {{ request()->routeIs('student-documents.*') ? 'active' : '' }}"
                        >
                            <i class="bi bi-file-earmark-check me-2"></i>
                            Document Verification
                        </a>

                    </div>

                </li>

            @endif


            {{-- =====================================================
                 COMPLAINTS - ADMIN / WARDEN / STAFF
            ====================================================== --}}

            @if(auth()->user()?->hasRole('admin') ||
                auth()->user()?->hasRole('warden') ||
                auth()->user()?->hasRole('staff'))

                @php
                    $complaintOpen =
                        request()->routeIs('complaints.*') ||
                        request()->routeIs('complaint-categories.*');
                @endphp

                <li class="nav-item">

                    <button
                        class="dropdown-toggle"
                        type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#complaintMenu"
                        aria-expanded="{{ $complaintOpen ? 'true' : 'false' }}"
                    >
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Complaints
                    </button>

                    <div
                        id="complaintMenu"
                        class="collapse submenu {{ $complaintOpen ? 'show' : '' }}"
                    >

                        <a
                            href="{{ route('complaints.index') }}"
                            class="nav-link {{ request()->routeIs('complaints.*') ? 'active' : '' }}"
                        >
                            <i class="bi bi-list-check me-2"></i>
                            All Complaints
                        </a>

                        <a
                            href="{{ route('complaint-categories.index') }}"
                            class="nav-link {{ request()->routeIs('complaint-categories.*') ? 'active' : '' }}"
                        >
                            <i class="bi bi-tags me-2"></i>
                            Categories
                        </a>

                    </div>

                </li>

            @endif


            {{-- =====================================================
                 NOTICES - ALL AUTHENTICATED USERS
            ====================================================== --}}

            @php
                $noticeOpen = request()->routeIs('notices.*');
            @endphp

            <li class="nav-item">

                <button
                    class="dropdown-toggle"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#noticeMenu"
                    aria-expanded="{{ $noticeOpen ? 'true' : 'false' }}"
                >
                    <i class="bi bi-megaphone me-2"></i>
                    Notices
                </button>

                <div
                    id="noticeMenu"
                    class="collapse submenu {{ $noticeOpen ? 'show' : '' }}"
                >

                    <a
                        href="{{ route('notices.index') }}"
                        class="nav-link {{ request()->routeIs('notices.index') ? 'active' : '' }}"
                    >
                        <i class="bi bi-megaphone me-2"></i>
                        Notice Board
                    </a>


                    @if(auth()->user()?->hasRole('admin') ||
                        auth()->user()?->hasRole('warden'))

                        <a
                            href="{{ route('notices.manage') }}"
                            class="nav-link {{ request()->routeIs('notices.manage') ? 'active' : '' }}"
                        >
                            <i class="bi bi-pencil-square me-2"></i>
                            Manage Notices
                        </a>

                    @endif

                </div>

            </li>


            {{-- =====================================================
                 VISITORS - ADMIN / WARDEN / STAFF
            ====================================================== --}}

            @if(auth()->user()?->hasRole('admin') ||
                auth()->user()?->hasRole('warden') ||
                auth()->user()?->hasRole('staff'))

                <li class="nav-item">

                    <a
                        href="{{ route('visitors.index') }}"
                        class="nav-link {{ request()->routeIs('visitors.*') ? 'active' : '' }}"
                    >
                        <i class="bi bi-person-walking me-2"></i>
                        Visitors
                    </a>

                </li>

            @endif


            {{-- =====================================================
                 ATTENDANCE - ADMIN / WARDEN / STAFF
            ====================================================== --}}

            @if(auth()->user()?->hasRole('admin') ||
                auth()->user()?->hasRole('warden') ||
                auth()->user()?->hasRole('staff'))

                @php
                    $attendanceOpen =
                        request()->routeIs('attendance.*') ||
                        request()->routeIs('leave-requests.*') ||
                        request()->routeIs('attendance.qr-scanner');
                @endphp

                <li class="nav-item">

                    <button
                        class="dropdown-toggle"
                        type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#attendanceMenu"
                        aria-expanded="{{ $attendanceOpen ? 'true' : 'false' }}"
                    >
                        <i class="bi bi-calendar-check me-2"></i>
                        Attendance
                    </button>

                    <div
                        id="attendanceMenu"
                        class="collapse submenu {{ $attendanceOpen ? 'show' : '' }}"
                    >

                        <a
                            href="{{ route('attendance.index') }}"
                            class="nav-link {{ request()->routeIs('attendance.*') ? 'active' : '' }}"
                        >
                            <i class="bi bi-calendar-check me-2"></i>
                            Attendance
                        </a>

                        <a
                            href="{{ route('leave-requests.index') }}"
                            class="nav-link {{ request()->routeIs('leave-requests.*') ? 'active' : '' }}"
                        >
                            <i class="bi bi-envelope-paper me-2"></i>
                            Leave Requests
                        </a>

                        <a
                            href="{{ route('attendance.qr-scanner') }}"
                            class="nav-link {{ request()->routeIs('attendance.qr-scanner') ? 'active' : '' }}"
                        >
                            <i class="bi bi-qr-code-scan me-2"></i>
                            QR Scanner
                        </a>

                    </div>

                </li>

            @endif


            {{-- =====================================================
                 MESS MANAGEMENT - ADMIN / WARDEN
            ====================================================== --}}

            @if(auth()->user()?->hasRole('admin') ||
                auth()->user()?->hasRole('warden'))

                @php
                    $messOpen =
                        request()->routeIs('meal-menu.*') ||
                        request()->routeIs('mess-cuts.*') ||
                        request()->routeIs('mess-bills.*');
                @endphp

                <li class="nav-item">

                    <button
                        class="dropdown-toggle"
                        type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#messMenu"
                        aria-expanded="{{ $messOpen ? 'true' : 'false' }}"
                    >
                        <i class="bi bi-egg-fried me-2"></i>
                        Mess Management
                    </button>

                    <div
                        id="messMenu"
                        class="collapse submenu {{ $messOpen ? 'show' : '' }}"
                    >

                        <a
                            href="{{ route('meal-menu.index') }}"
                            class="nav-link {{ request()->routeIs('meal-menu.*') ? 'active' : '' }}"
                        >
                            <i class="bi bi-egg-fried me-2"></i>
                            Meal Menu
                        </a>

                        <a
                            href="{{ route('mess-cuts.index') }}"
                            class="nav-link {{ request()->routeIs('mess-cuts.*') ? 'active' : '' }}"
                        >
                            <i class="bi bi-cup-hot me-2"></i>
                            Mess Cuts
                        </a>

                        <a
                            href="{{ route('mess-bills.create') }}"
                            class="nav-link {{ request()->routeIs('mess-bills.*') ? 'active' : '' }}"
                        >
                            <i class="bi bi-receipt me-2"></i>
                            Mess Bills
                        </a>

                    </div>

                </li>

            @endif


            {{-- =====================================================
                 REPORTS & ANALYTICS - ADMIN / WARDEN
            ====================================================== --}}

            @if(auth()->user()?->hasRole('admin') ||
                auth()->user()?->hasRole('warden'))

                @php
                    $reportsOpen =
                        request()->routeIs('reports.*') ||
                        request()->routeIs('smart-search.*');
                @endphp

                <li class="nav-item">

                    <button
                        class="dropdown-toggle"
                        type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#reportsMenu"
                        aria-expanded="{{ $reportsOpen ? 'true' : 'false' }}"
                    >
                        <i class="bi bi-bar-chart-line me-2"></i>
                        Reports & Analytics
                    </button>

                    <div
                        id="reportsMenu"
                        class="collapse submenu {{ $reportsOpen ? 'show' : '' }}"
                    >

                        <a
                            href="{{ route('reports.index') }}"
                            class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}"
                        >
                            <i class="bi bi-bar-chart-line me-2"></i>
                            Reports
                        </a>

                        <a
                            href="{{ route('smart-search.index') }}"
                            class="nav-link {{ request()->routeIs('smart-search.*') ? 'active' : '' }}"
                        >
                            <i class="bi bi-search-heart me-2"></i>
                            Smart Search
                        </a>

                    </div>

                </li>

            @endif


            {{-- =====================================================
                 INVENTORY MANAGEMENT - ADMIN / WARDEN
            ====================================================== --}}

            @if(auth()->user()?->hasRole('admin') ||
                auth()->user()?->hasRole('warden'))

                @php
                    $inventoryOpen =
                        request()->routeIs('assets.*') ||
                        request()->routeIs('asset-categories.*') ||
                        request()->routeIs('asset-damage-reports.*');
                @endphp

                <li class="menu-title">
                    Inventory Management
                </li>

                <li class="nav-item">

                    <button
                        class="dropdown-toggle"
                        type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#inventoryMenu"
                        aria-expanded="{{ $inventoryOpen ? 'true' : 'false' }}"
                    >
                        <i class="bi bi-box-seam me-2"></i>
                        Inventory
                    </button>

                    <div
                        id="inventoryMenu"
                        class="collapse submenu {{ $inventoryOpen ? 'show' : '' }}"
                    >

                        <a
                            href="{{ route('assets.index') }}"
                            class="nav-link {{ request()->routeIs('assets.index') ? 'active' : '' }}"
                        >
                            <i class="bi bi-box-seam me-2"></i>
                            Assets
                        </a>

                        <a
                            href="{{ route('asset-categories.index') }}"
                            class="nav-link {{ request()->routeIs('asset-categories.*') ? 'active' : '' }}"
                        >
                            <i class="bi bi-tags me-2"></i>
                            Asset Categories
                        </a>

                        <a
                            href="{{ route('assets.create') }}"
                            class="nav-link {{ request()->routeIs('assets.create') ? 'active' : '' }}"
                        >
                            <i class="bi bi-plus-circle me-2"></i>
                            Add Asset
                        </a>

                        <a
                            href="{{ route('asset-damage-reports.index') }}"
                            class="nav-link {{ request()->routeIs('asset-damage-reports.*') ? 'active' : '' }}"
                        >
                            <i class="bi bi-tools me-2"></i>
                            Damage Reports
                        </a>

                    </div>

                </li>

            @endif


            {{-- =====================================================
                 ADMINISTRATION - ADMIN ONLY
            ====================================================== --}}

            @if(auth()->user()?->hasRole('admin'))

                @php
                    $adminOpen =
                        request()->routeIs('users.*') ||
                        request()->routeIs('roles.*') ||
                        request()->routeIs('notification-logs.*') ||
                        request()->routeIs('policy-documents.*') ||
                        request()->routeIs('activity-logs.*');
                @endphp

                <li class="menu-title">
                    Administration
                </li>

                <li class="nav-item">

                    <button
                        class="dropdown-toggle"
                        type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#adminMenu"
                        aria-expanded="{{ $adminOpen ? 'true' : 'false' }}"
                    >
                        <i class="bi bi-gear me-2"></i>
                        Administration
                    </button>

                    <div
                        id="adminMenu"
                        class="collapse submenu {{ $adminOpen ? 'show' : '' }}"
                    >

                        <a
                            href="{{ route('users.index') }}"
                            class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}"
                        >
                            <i class="bi bi-people me-2"></i>
                            Users
                        </a>

                        <a
                            href="{{ route('roles.index') }}"
                            class="nav-link {{ request()->routeIs('roles.*') ? 'active' : '' }}"
                        >
                            <i class="bi bi-shield-lock me-2"></i>
                            Roles & Permissions
                        </a>

                        <a
                            href="{{ route('notification-logs.index') }}"
                            class="nav-link {{ request()->routeIs('notification-logs.*') ? 'active' : '' }}"
                        >
                            <i class="bi bi-send-check me-2"></i>
                            Notification Logs
                        </a>

                        <a
                            href="{{ route('policy-documents.index') }}"
                            class="nav-link {{ request()->routeIs('policy-documents.*') ? 'active' : '' }}"
                        >
                            <i class="bi bi-journal-richtext me-2"></i>
                            Policy Documents
                        </a>

                        <a
                            href="{{ route('activity-logs.index') }}"
                            class="nav-link {{ request()->routeIs('activity-logs.*') ? 'active' : '' }}"
                        >
                            <i class="bi bi-clock-history me-2"></i>
                            Activity Logs
                        </a>

                    </div>

                </li>

            @endif


            {{-- =====================================================
                 STUDENT PORTAL
            ====================================================== --}}

            @if(auth()->user()?->hasRole('student'))

                <li class="menu-title">
                    Student Portal
                </li>


                {{-- My Portal --}}
                <li class="nav-item">

                    <a
                        href="{{ route('student-portal.dashboard') }}"
                        class="nav-link {{ request()->routeIs('student-portal.dashboard') ? 'active' : '' }}"
                    >
                        <i class="bi bi-person-circle me-2"></i>
                        My Portal
                    </a>

                </li>


                {{-- My Profile --}}
                <li class="nav-item">

                    <a
                        href="{{ route('student-portal.profile') }}"
                        class="nav-link {{ request()->routeIs('student-portal.profile') ? 'active' : '' }}"
                    >
                        <i class="bi bi-person-vcard me-2"></i>
                        My Profile
                    </a>

                </li>


                {{-- My Complaints --}}
                <li class="nav-item">

                    <a
                        href="{{ route('student-portal.complaints') }}"
                        class="nav-link {{ request()->routeIs('student-portal.complaints*') ? 'active' : '' }}"
                    >
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        My Complaints
                    </a>

                </li>


                {{-- My Attendance --}}
                <li class="nav-item">

                    <a
                        href="{{ route('student-portal.attendance') }}"
                        class="nav-link {{ request()->routeIs('student-portal.attendance') ? 'active' : '' }}"
                    >
                        <i class="bi bi-calendar-check me-2"></i>
                        My Attendance
                    </a>

                </li>


                {{-- Leave Requests --}}
                <li class="nav-item">

                    <a
                        href="{{ route('student-portal.leave-requests') }}"
                        class="nav-link {{ request()->routeIs('student-portal.leave-requests*') ? 'active' : '' }}"
                    >
                        <i class="bi bi-envelope-paper me-2"></i>
                        Leave Requests
                    </a>

                </li>


                {{-- Meal Menu --}}
                <li class="nav-item">

                    <a
                        href="{{ route('student-portal.meal-menu') }}"
                        class="nav-link {{ request()->routeIs('student-portal.meal-menu') ? 'active' : '' }}"
                    >
                        <i class="bi bi-egg-fried me-2"></i>
                        Meal Menu
                    </a>

                </li>


                {{-- Mess Cuts --}}
                <li class="nav-item">

                    <a
                        href="{{ route('student-portal.mess-cuts') }}"
                        class="nav-link {{ request()->routeIs('student-portal.mess-cuts*') ? 'active' : '' }}"
                    >
                        <i class="bi bi-cup-hot me-2"></i>
                        Mess Cuts
                    </a>

                </li>


                {{-- AI Chatbot --}}
                <li class="nav-item">

                    <a
                        href="{{ route('student-portal.chatbot.history') }}"
                        class="nav-link {{ request()->routeIs('student-portal.chatbot.*') ? 'active' : '' }}"
                    >
                        <i class="bi bi-robot me-2"></i>
                        AI Chatbot
                    </a>

                </li>


                {{-- Ask About Rules --}}
                <li class="nav-item">

                    <a
                        href="{{ route('student-portal.policy-qa') }}"
                        class="nav-link {{ request()->routeIs('student-portal.policy-qa*') ? 'active' : '' }}"
                    >
                        <i class="bi bi-journal-text me-2"></i>
                        Ask About Rules
                    </a>

                </li>


                {{-- My Documents --}}
                <li class="nav-item">

                    <a
                        href="{{ route('student-portal.documents.index') }}"
                        class="nav-link {{ request()->routeIs('student-portal.documents.*') ? 'active' : '' }}"
                    >
                        <i class="bi bi-file-earmark-person me-2"></i>
                        My Documents
                    </a>

                </li>


                {{-- Notices --}}
                <li class="nav-item">

                    <a
                        href="{{ route('notices.index') }}"
                        class="nav-link {{ request()->routeIs('notices.index') ? 'active' : '' }}"
                    >
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

    <div class="flex-grow-1 content-wrapper">

        {{-- Top Navbar --}}
        <nav class="navbar navbar-light bg-white shadow-sm px-3">

            <span class="navbar-text fw-semibold">
                @yield('page-title', 'Dashboard')
            </span>


            <div class="dropdown">

                <button
                    class="btn btn-light dropdown-toggle"
                    type="button"
                    data-bs-toggle="dropdown"
                >

                    <i class="bi bi-person-circle"></i>

                    {{ auth()->user()->name ?? '' }}

                </button>


                <ul class="dropdown-menu dropdown-menu-end">

                    <li>

                        <form
                            action="{{ route('logout') }}"
                            method="POST"
                        >

                            @csrf

                            <button
                                class="dropdown-item"
                                type="submit"
                            >

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

                <div class="alert alert-success">
                    {{ session('status') }}
                </div>

            @endif


            @if(session('success'))

                <div class="alert alert-success">
                    {{ session('success') }}
                </div>

            @endif


            @if(session('error'))

                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>

            @endif


            @yield('content')

        </div>

    </div>

</div>


{{-- =============================================================
     JAVASCRIPT
============================================================== --}}

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>


<script>

document.addEventListener('DOMContentLoaded', function () {

    /*
    |--------------------------------------------------------------------------
    | Sidebar menu handling
    |--------------------------------------------------------------------------
    */

    const sidebar = document.querySelector('.sidebar');

    if (!sidebar) {
        return;
    }


    const dropdownButtons =
        sidebar.querySelectorAll('.dropdown-toggle');


    dropdownButtons.forEach(function (button) {

        button.addEventListener('click', function () {

            const targetSelector =
                button.getAttribute('data-bs-target');

            const target =
                document.querySelector(targetSelector);

            if (!target) {
                return;
            }


            /*
             * Bootstrap handles the actual collapse.
             */

            setTimeout(function () {

                const isOpen =
                    target.classList.contains('show');

                button.setAttribute(
                    'aria-expanded',
                    isOpen ? 'true' : 'false'
                );

            }, 250);

        });

    });


    /*
    |--------------------------------------------------------------------------
    | Keep active submenu open
    |--------------------------------------------------------------------------
    */

    sidebar
        .querySelectorAll('.submenu .nav-link.active')
        .forEach(function (activeLink) {

            const submenu =
                activeLink.closest('.submenu');

            if (!submenu) {
                return;
            }


            submenu.classList.add('show');


            const button =
                document.querySelector(
                    '[data-bs-target="#' + submenu.id + '"]'
                );


            if (button) {

                button.setAttribute(
                    'aria-expanded',
                    'true'
                );

            }

        });

});

</script>


@stack('scripts')

</body>

</html>
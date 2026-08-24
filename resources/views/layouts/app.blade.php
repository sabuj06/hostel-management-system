<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') - Hostel Management</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        body { background:#f4f6f9; }
        .sidebar { min-height:100vh; background:#1e2a3a; }
        .sidebar a { color:#c9d3de; }
        .sidebar a.active, .sidebar a:hover { color:#fff; background:#2c3e50; }
        .navbar-brand { font-weight:600; }
    </style>
    @stack('styles')
</head>
<body>
<div class="d-flex">
    <!-- Sidebar -->
    <nav class="sidebar p-3" style="width:240px;">
        <div class="text-white fs-5 fw-bold mb-4"><i class="bi bi-building"></i> Hostel MS</div>
        <ul class="nav nav-pills flex-column gap-1">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                    <i class="bi bi-speedometer2 me-2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('notices.*') ? 'active' : '' }}" href="{{ route('notices.index') }}">
                    <i class="bi bi-megaphone me-2"></i> Notice Board
                </a>
            </li>
            @if(auth()->user()?->hasRole('admin') || auth()->user()?->hasRole('warden'))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}" href="{{ route('reports.index') }}">
                    <i class="bi bi-bar-chart-line me-2"></i> Reports & Analytics
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('smart-search.*') ? 'active' : '' }}" href="{{ route('smart-search.index') }}">
                    <i class="bi bi-search-heart me-2"></i> Smart Search (AI)
                </a>
            </li>
            @endif
            @if(auth()->user()?->hasRole('admin'))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('users.*') || request()->routeIs('roles.*') ? 'active' : '' }}" href="{{ route('users.index') }}">
                    <i class="bi bi-people me-2"></i> Users & Roles
                </a>
            </li>
            @endif
            @if(auth()->user()?->hasRole('admin') || auth()->user()?->hasRole('warden'))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('student-documents.*') ? 'active' : '' }}" href="{{ route('student-documents.index') }}">
                    <i class="bi bi-file-earmark-check me-2"></i> Document Verification
                </a>
            </li>
            @endif
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('students.*') ? 'active' : '' }}" href="{{ route('students.index') }}">
                    <i class="bi bi-person-badge me-2"></i> Students
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('room-allocations.*') ? 'active' : '' }}" href="{{ route('room-allocations.index') }}">
                    <i class="bi bi-arrow-left-right me-2"></i> Room Allocation
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('hostels.*') ? 'active' : '' }}" href="{{ route('hostels.index') }}">
                    <i class="bi bi-building me-2"></i> Hostels
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('blocks.*') ? 'active' : '' }}" href="{{ route('blocks.index') }}">
                    <i class="bi bi-grid-1x2 me-2"></i> Blocks
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('floors.*') ? 'active' : '' }}" href="{{ route('floors.index') }}">
                    <i class="bi bi-layers me-2"></i> Floors
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('rooms.*') ? 'active' : '' }}" href="{{ route('rooms.index') }}">
                    <i class="bi bi-door-open me-2"></i> Rooms
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('beds.*') ? 'active' : '' }}" href="{{ route('beds.index') }}">
                    <i class="bi bi-grid-3x3-gap me-2"></i> Beds
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('invoices.*') ? 'active' : '' }}" href="{{ route('invoices.index') }}">
                    <i class="bi bi-receipt me-2"></i> Invoices
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('fee-structures.*') ? 'active' : '' }}" href="{{ route('fee-structures.index') }}">
                    <i class="bi bi-cash-coin me-2"></i> Fee Structures
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('complaints.*') ? 'active' : '' }}" href="{{ route('complaints.index') }}">
                    <i class="bi bi-exclamation-triangle me-2"></i> Complaints
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('visitors.*') ? 'active' : '' }}" href="{{ route('visitors.index') }}">
                    <i class="bi bi-person-walking me-2"></i> Visitors
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('attendance.*') ? 'active' : '' }}" href="{{ route('attendance.index') }}">
                    <i class="bi bi-calendar-check me-2"></i> Attendance
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('leave-requests.*') ? 'active' : '' }}" href="{{ route('leave-requests.index') }}">
                    <i class="bi bi-envelope-paper me-2"></i> Leave Requests
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('meal-menu.*') ? 'active' : '' }}" href="{{ route('meal-menu.index') }}">
                    <i class="bi bi-egg-fried me-2"></i> Meal Menu
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('mess-cuts.*') || request()->routeIs('mess-bills.*') ? 'active' : '' }}" href="{{ route('mess-cuts.index') }}">
                    <i class="bi bi-cup-hot me-2"></i> Mess Management
                </a>
            </li>
            @if(auth()->user()?->hasRole('admin'))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('notification-logs.*') ? 'active' : '' }}" href="{{ route('notification-logs.index') }}">
                    <i class="bi bi-send-check me-2"></i> Notification Logs
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('policy-documents.*') ? 'active' : '' }}" href="{{ route('policy-documents.index') }}">
                    <i class="bi bi-journal-richtext me-2"></i> Policy Documents (RAG)
                </a>
            </li>
            @endif
        </ul>
    </nav>

    <!-- Main content -->
    <div class="flex-grow-1">
        <nav class="navbar navbar-light bg-white shadow-sm px-3">
            <span class="navbar-text">@yield('page-title', 'Dashboard')</span>
            <div class="dropdown">
                <button class="btn btn-light dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="bi bi-person-circle"></i> {{ auth()->user()->name ?? '' }}
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button class="dropdown-item" type="submit">Logout</button>
                        </form>
                    </li>
                </ul>
            </div>
        </nav>

        <div class="p-4">
            @if(session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif
            @yield('content')
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
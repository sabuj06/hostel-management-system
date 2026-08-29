<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', 'My Portal') - Hostel Management</title>

    <!-- Bootstrap -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- Bootstrap Icons -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
    >

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <style>
        body {
            background: #f4f6f9;
            margin: 0;
        }

        .sidebar {
            min-height: 100vh;
            width: 250px;
            flex-shrink: 0;
            background: #1e2a3a;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.08);
        }

        .sidebar-logo {
            color: #ffffff;
            font-size: 20px;
            font-weight: 700;
            padding: 8px 10px;
        }

        .sidebar a {
            text-decoration: none;
        }

        .sidebar .nav-link {
            color: #c9d3de;
            border-radius: 8px;
            padding: 9px 12px;
            transition: all 0.2s ease;
        }

        .sidebar .nav-link:hover {
            color: #ffffff;
            background: #2c3e50;
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
            background: #2c3e50;
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
            background: #2c3e50;
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

        /* Floating chatbot */

        #chatbot-widget {
            position: fixed;
            right: 20px;
            bottom: 20px;
            z-index: 9999;
        }

        #chatbot-toggle {
            width: 48px;
            height: 48px;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        #chatbot-panel {
            display: none;
            position: absolute;
            right: 0;
            bottom: 58px;

            width: 280px;
            height: 360px;

            border-radius: 12px;
            overflow: hidden;
        }

        #chatbot-panel .card-header {
            padding: 8px 10px;
            font-size: 13px;
        }

        #chatbot-messages {
            height: 255px;
            overflow-y: auto;
            background: #f8f9fa;
            padding: 8px;
        }

        .chat-bubble {
            max-width: 88%;
            padding: 7px 10px;
            border-radius: 10px;
            margin-bottom: 7px;
            font-size: 12px;
            line-height: 1.4;
            white-space: pre-line;
            word-wrap: break-word;
        }

        .chat-bubble.user {
            background: #0d6efd;
            color: white;
            margin-left: auto;
            border-bottom-right-radius: 2px;
        }

        .chat-bubble.assistant {
            background: white;
            color: #212529;
            margin-right: auto;
            border: 1px solid #e9ecef;
            border-bottom-left-radius: 2px;
        }

        #chatbot-panel .card-footer {
            padding: 7px;
        }

        #chatbot-input {
            font-size: 12px;
            height: 32px;
        }

        #chatbot-form button {
            width: 34px;
            height: 32px;
            padding: 0;
        }

        @media (max-width: 576px) {

            #chatbot-widget {
                right: 15px;
                bottom: 15px;
            }

            #chatbot-panel {
                width: 270px;
                height: 350px;
                right: 0;
            }

            #chatbot-messages {
                height: 245px;
            }
        }
    </style>

    @stack('styles')
</head>

<body>

<div class="d-flex">

    <!-- Sidebar -->
    <nav class="sidebar p-3">

        <div class="sidebar-logo mb-4">
            <i class="bi bi-mortarboard me-2"></i>
            My Portal
        </div>

        <ul class="nav nav-pills flex-column gap-1">

            <!-- Dashboard -->
            <li class="nav-item">
                <a
                    class="nav-link {{ request()->routeIs('student-portal.dashboard') ? 'active' : '' }}"
                    href="{{ route('student-portal.dashboard') }}"
                >
                    <i class="bi bi-speedometer2 me-2"></i>
                    Dashboard
                </a>
            </li>

            <!-- Profile -->
            <li class="nav-item">
                <a
                    class="nav-link {{ request()->routeIs('student-portal.profile') ? 'active' : '' }}"
                    href="{{ route('student-portal.profile') }}"
                >
                    <i class="bi bi-person me-2"></i>
                    My Profile
                </a>
            </li>

            <!-- Invoices -->
            <li class="nav-item">
                <a
                    class="nav-link {{ request()->routeIs('student-portal.invoices') ? 'active' : '' }}"
                    href="{{ route('student-portal.invoices') }}"
                >
                    <i class="bi bi-receipt me-2"></i>
                    My Invoices
                </a>
            </li>

            <!-- Complaints -->
            <li class="nav-item">
                <a
                    class="nav-link {{ request()->routeIs('student-portal.complaints*') ? 'active' : '' }}"
                    href="{{ route('student-portal.complaints') }}"
                >
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    My Complaints
                </a>
            </li>

            <!-- =====================================================
                 ATTENDANCE & LEAVE (Dropdown Group)
            ====================================================== -->
            @php
                $attendanceOpen =
                    request()->routeIs('student-portal.attendance') ||
                    request()->routeIs('student-portal.my-qr-code') ||
                    request()->routeIs('student-portal.leave-requests*');
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
                    Attendance & Leave
                </button>

                <div
                    id="attendanceMenu"
                    class="collapse submenu {{ $attendanceOpen ? 'show' : '' }}"
                >

                    <a
                        class="nav-link {{ request()->routeIs('student-portal.attendance') ? 'active' : '' }}"
                        href="{{ route('student-portal.attendance') }}"
                    >
                        <i class="bi bi-calendar-check me-2"></i>
                        My Attendance
                    </a>

                    <a
                        class="nav-link {{ request()->routeIs('student-portal.my-qr-code') ? 'active' : '' }}"
                        href="{{ route('student-portal.my-qr-code') }}"
                    >
                        <i class="bi bi-qr-code me-2"></i>
                        My QR Code
                    </a>

                    <a
                        class="nav-link {{ request()->routeIs('student-portal.leave-requests*') ? 'active' : '' }}"
                        href="{{ route('student-portal.leave-requests') }}"
                    >
                        <i class="bi bi-envelope-paper me-2"></i>
                        Leave Requests
                    </a>

                </div>

            </li>

            <!-- =====================================================
                 MESS (Dropdown Group)
            ====================================================== -->
            @php
                $messOpen =
                    request()->routeIs('student-portal.meal-menu') ||
                    request()->routeIs('student-portal.mess-cuts*');
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
                    Mess
                </button>

                <div
                    id="messMenu"
                    class="collapse submenu {{ $messOpen ? 'show' : '' }}"
                >

                    <a
                        class="nav-link {{ request()->routeIs('student-portal.meal-menu') ? 'active' : '' }}"
                        href="{{ route('student-portal.meal-menu') }}"
                    >
                        <i class="bi bi-egg-fried me-2"></i>
                        Meal Menu
                    </a>

                    <a
                        class="nav-link {{ request()->routeIs('student-portal.mess-cuts*') ? 'active' : '' }}"
                        href="{{ route('student-portal.mess-cuts') }}"
                    >
                        <i class="bi bi-cup-hot me-2"></i>
                        Mess Cuts
                    </a>

                </div>

            </li>

            <!-- =====================================================
                 ASSISTANT (Dropdown Group)
            ====================================================== -->
            @php
                $assistantOpen =
                    request()->routeIs('student-portal.chatbot*') ||
                    request()->routeIs('student-portal.policy-qa*');
            @endphp

            <li class="nav-item">

                <button
                    class="dropdown-toggle"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#assistantMenu"
                    aria-expanded="{{ $assistantOpen ? 'true' : 'false' }}"
                >
                    <i class="bi bi-robot me-2"></i>
                    Assistant
                </button>

                <div
                    id="assistantMenu"
                    class="collapse submenu {{ $assistantOpen ? 'show' : '' }}"
                >

                    

                    <a
                        class="nav-link {{ request()->routeIs('student-portal.policy-qa*') ? 'active' : '' }}"
                        href="{{ route('student-portal.policy-qa') }}"
                    >
                        <i class="bi bi-journal-text me-2"></i>
                        Ask About Rules
                    </a>

                </div>

            </li>

            <!-- My Documents -->
            <li class="nav-item">
                <a
                    class="nav-link {{ request()->routeIs('student-portal.documents*') ? 'active' : '' }}"
                    href="{{ route('student-portal.documents.index') }}"
                >
                    <i class="bi bi-file-earmark-person me-2"></i>
                    My Documents
                </a>
            </li>

            <!-- Notice Board -->
            <li class="nav-item">
                <a
                    class="nav-link {{ request()->routeIs('notices*') ? 'active' : '' }}"
                    href="{{ route('notices.index') }}"
                >
                    <i class="bi bi-megaphone me-2"></i>
                    Notice Board
                </a>
            </li>

        </ul>
    </nav>


    <!-- Main Content -->
    <div class="flex-grow-1">

        <!-- Top Navbar -->
        <nav class="navbar navbar-light bg-white shadow-sm px-3">

            <span class="navbar-text">
                @yield('page-title', 'My Portal')
            </span>

            <div class="dropdown">

                <button
                    class="btn btn-light dropdown-toggle"
                    data-bs-toggle="dropdown"
                    type="button"
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
                                Logout
                            </button>

                        </form>

                    </li>

                </ul>

            </div>

        </nav>


        <!-- Page Content -->
        <div class="p-4">

            @if(session('status'))

                <div class="alert alert-success">
                    {{ session('status') }}
                </div>

            @endif

            @yield('content')

        </div>

    </div>

</div>


<!-- Floating AI Chatbot -->
<div id="chatbot-widget">

    <!-- Small Chat Button -->
    <button
        id="chatbot-toggle"
        type="button"
        class="btn btn-primary rounded-circle shadow"
        title="Chat with Hostel Assistant"
    >
        <i class="bi bi-robot fs-4"></i>
    </button>


    <!-- Chat Window -->
    <div
        id="chatbot-panel"
        class="card shadow-lg border-0"
    >

        <!-- Header -->
        <div
            class="card-header bg-primary text-white d-flex justify-content-between align-items-center"
        >

            <div>

                <i class="bi bi-stars me-1"></i>

                <strong>
                    Hostel Assistant
                </strong>

                <div style="font-size: 11px;">
                    AI Assistant
                </div>

            </div>

            <button
                type="button"
                id="chatbot-close"
                class="btn-close btn-close-white"
            ></button>

        </div>


        <!-- Messages -->
        <div
            id="chatbot-messages"
            class="card-body"
        ></div>


        <!-- Input -->
        <div class="card-footer bg-white">

            <form
                id="chatbot-form"
                class="d-flex gap-2"
            >

                <input
                    type="text"
                    id="chatbot-input"
                    class="form-control"
                    placeholder="Ask something..."
                    autocomplete="off"
                >

                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    <i class="bi bi-send-fill"></i>
                </button>

            </form>

        </div>

    </div>

</div>


<script>
$(function () {

    /*
    |--------------------------------------------------------------------------
    | CSRF Setup
    |--------------------------------------------------------------------------
    */

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });


    let historyLoaded = false;


    function appendBubble(role, text) {

        const bubble = $('<div>')
            .addClass('chat-bubble')
            .addClass(role)
            .text(text);

        $('#chatbot-messages').append(bubble);

        const messages = $('#chatbot-messages')[0];

        if (messages) {
            messages.scrollTop = messages.scrollHeight;
        }
    }


    function loadHistory() {

        if (historyLoaded) {
            return;
        }

        historyLoaded = true;

        $.getJSON(
            '{{ route("student-portal.chatbot.history") }}',
            function (response) {

                if (
                    !response.messages ||
                    response.messages.length === 0
                ) {

                    appendBubble(
                        'assistant',
                        "Hello! 👋 I'm your Hostel Assistant. How can I help you?"
                    );

                    return;
                }


                response.messages.forEach(function (message) {

                    appendBubble(
                        message.role,
                        message.message
                    );

                });

            }
        ).fail(function () {

            appendBubble(
                'assistant',
                "Hello! 👋 I'm your Hostel Assistant. How can I help you?"
            );

        });
    }


    $('#chatbot-toggle').on('click', function () {

        $('#chatbot-panel').fadeToggle(200);

        loadHistory();

    });


    $('#chatbot-close').on('click', function () {

        $('#chatbot-panel').fadeOut(200);

    });


    $('#chatbot-form').on('submit', function (event) {

        event.preventDefault();


        const input = $('#chatbot-input');

        const message = input.val().trim();


        if (!message) {
            return;
        }


        appendBubble(
            'user',
            message
        );


        input
            .val('')
            .prop('disabled', true);


        const typing = $('<div>')
            .addClass('chat-bubble assistant')
            .text('Typing...');


        $('#chatbot-messages').append(typing);


        const messages = $('#chatbot-messages')[0];

        if (messages) {
            messages.scrollTop = messages.scrollHeight;
        }


        $.ajax({

            url: '{{ route("student-portal.chatbot.ask") }}',

            method: 'POST',

            data: {
                message: message
            },


            success: function (response) {

                typing.remove();


                let answer =
                    response?.message?.message;


                if (!answer) {

                    answer =
                        'Sorry, I could not generate a response.';

                }


                appendBubble(
                    'assistant',
                    answer
                );

            },


            error: function (xhr) {

                typing.remove();


                appendBubble(
                    'assistant',
                    'Sorry, something went wrong. Please try again.'
                );


                console.error(
                    xhr.responseText
                );

            },


            complete: function () {

                input
                    .prop('disabled', false)
                    .focus();

            }

        });

    });

});
</script>


<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>


<script>

document.addEventListener('DOMContentLoaded', function () {

    const sidebar = document.querySelector('.sidebar');

    if (!sidebar) {
        return;
    }

    const dropdownButtons = sidebar.querySelectorAll('.dropdown-toggle');

    dropdownButtons.forEach(function (button) {

        button.addEventListener('click', function () {

            const targetSelector = button.getAttribute('data-bs-target');
            const target = document.querySelector(targetSelector);

            if (!target) {
                return;
            }

            setTimeout(function () {

                const isOpen = target.classList.contains('show');

                button.setAttribute('aria-expanded', isOpen ? 'true' : 'false');

            }, 250);

        });

    });

    sidebar
        .querySelectorAll('.submenu .nav-link.active')
        .forEach(function (activeLink) {

            const submenu = activeLink.closest('.submenu');

            if (!submenu) {
                return;
            }

            submenu.classList.add('show');

            const button = document.querySelector(
                '[data-bs-target="#' + submenu.id + '"]'
            );

            if (button) {
                button.setAttribute('aria-expanded', 'true');
            }

        });

});

</script>


@stack('scripts')

</body>
</html>
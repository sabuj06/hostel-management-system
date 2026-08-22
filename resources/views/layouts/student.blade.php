<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'My Portal') - Hostel Management</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        body { background:#f4f6f9; }
        .sidebar { min-height:100vh; background:#1e2a3a; }
        .sidebar a { color:#c9d3de; }
        .sidebar a.active, .sidebar a:hover { color:#fff; background:#2c3e50; }
    </style>
    @stack('styles')
</head>
<body>
<div class="d-flex">
    <nav class="sidebar p-3" style="width:240px;">
        <div class="text-white fs-5 fw-bold mb-4"><i class="bi bi-mortarboard"></i> My Portal</div>
        <ul class="nav nav-pills flex-column gap-1">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('student-portal.dashboard') ? 'active' : '' }}" href="{{ route('student-portal.dashboard') }}">
                    <i class="bi bi-speedometer2 me-2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('student-portal.profile') ? 'active' : '' }}" href="{{ route('student-portal.profile') }}">
                    <i class="bi bi-person me-2"></i> My Profile
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('student-portal.invoices') ? 'active' : '' }}" href="{{ route('student-portal.invoices') }}">
                    <i class="bi bi-receipt me-2"></i> My Invoices
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('student-portal.complaints*') ? 'active' : '' }}" href="{{ route('student-portal.complaints') }}">
                    <i class="bi bi-exclamation-triangle me-2"></i> My Complaints
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('student-portal.attendance') ? 'active' : '' }}" href="{{ route('student-portal.attendance') }}">
                    <i class="bi bi-calendar-check me-2"></i> My Attendance
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('student-portal.leave-requests*') ? 'active' : '' }}" href="{{ route('student-portal.leave-requests') }}">
                    <i class="bi bi-envelope-paper me-2"></i> Leave Requests
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('student-portal.meal-menu') ? 'active' : '' }}" href="{{ route('student-portal.meal-menu') }}">
                    <i class="bi bi-egg-fried me-2"></i> Meal Menu
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('student-portal.mess-cuts*') ? 'active' : '' }}" href="{{ route('student-portal.mess-cuts') }}">
                    <i class="bi bi-cup-hot me-2"></i> Mess Cuts
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('notices.*') ? 'active' : '' }}" href="{{ route('notices.index') }}">
                    <i class="bi bi-megaphone me-2"></i> Notice Board
                </a>
            </li>
        </ul>
    </nav>

    <div class="flex-grow-1">
        <nav class="navbar navbar-light bg-white shadow-sm px-3">
            <span class="navbar-text">@yield('page-title', 'My Portal')</span>
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

<!-- Floating AI Chatbot Widget -->
<div id="chatbot-widget">
    <button id="chatbot-toggle" class="btn btn-primary rounded-circle shadow" title="Ask the hostel assistant">
        <i class="bi bi-chat-dots-fill fs-4"></i>
    </button>

    <div id="chatbot-panel" class="card shadow-lg border-0" style="display:none;">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-2">
            <span><i class="bi bi-stars"></i> Hostel Assistant</span>
            <button id="chatbot-close" class="btn-close btn-close-white btn-sm"></button>
        </div>
        <div class="card-body p-2" id="chatbot-messages" style="height:340px; overflow-y:auto;"></div>
        <div class="card-footer p-2 bg-white">
            <form id="chatbot-form" class="d-flex gap-2">
                <input type="text" id="chatbot-input" class="form-control form-control-sm" placeholder="Ask about room, fees, mess menu..." autocomplete="off">
                <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-send"></i></button>
            </form>
        </div>
    </div>
</div>

<style>
    #chatbot-widget { position: fixed; bottom: 24px; right: 24px; z-index: 1050; }
    #chatbot-toggle { width: 56px; height: 56px; }
    #chatbot-panel { position: absolute; bottom: 68px; right: 0; width: 340px; border-radius: 12px; overflow: hidden; }
    .chat-bubble { max-width: 85%; padding: 8px 12px; border-radius: 12px; margin-bottom: 8px; font-size: 0.9rem; white-space: pre-line; }
    .chat-bubble.user { background: #4e73df; color: #fff; margin-left: auto; border-bottom-right-radius: 2px; }
    .chat-bubble.assistant { background: #f1f3f5; color: #212529; margin-right: auto; border-bottom-left-radius: 2px; }
</style>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    $(function () {
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

        let historyLoaded = false;

        function appendBubble(role, text) {
            const $bubble = $('<div>').addClass('chat-bubble').addClass(role).text(text);
            $('#chatbot-messages').append($bubble);
            $('#chatbot-messages').scrollTop($('#chatbot-messages')[0].scrollHeight);
        }

        function loadHistory() {
            if (historyLoaded) return;
            historyLoaded = true;

            $.getJSON('{{ route('student-portal.chatbot.history') }}', function (res) {
                if (res.messages.length === 0) {
                    appendBubble('assistant', "Hi! I'm your hostel assistant. Ask me about your room, fees, today's mess menu, or recent notices.");
                    return;
                }
                res.messages.forEach(m => appendBubble(m.role, m.message));
            });
        }

        $('#chatbot-toggle').on('click', function () {
            $('#chatbot-panel').toggle();
            loadHistory();
        });

        $('#chatbot-close').on('click', function () {
            $('#chatbot-panel').hide();
        });

        // Send a question via AJAX, append both sides of the conversation live
        $('#chatbot-form').on('submit', function (e) {
            e.preventDefault();
            const $input = $('#chatbot-input');
            const message = $input.val().trim();
            if (!message) return;

            appendBubble('user', message);
            $input.val('').prop('disabled', true);

            const $typing = $('<div>').addClass('chat-bubble assistant').text('Typing...');
            $('#chatbot-messages').append($typing);
            $('#chatbot-messages').scrollTop($('#chatbot-messages')[0].scrollHeight);

            $.ajax({
                url: '{{ route('student-portal.chatbot.ask') }}',
                method: 'POST',
                data: { message: message },
                success: function (res) {
                    $typing.remove();
                    appendBubble('assistant', res.message.message);
                },
                error: function () {
                    $typing.remove();
                    appendBubble('assistant', 'Sorry, something went wrong. Please try again.');
                },
                complete: function () {
                    $input.prop('disabled', false).focus();
                }
            });
        });
    });
</script>
@stack('scripts')
</body>
</html>
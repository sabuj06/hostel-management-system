<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NotificationLog;
use Illuminate\Http\Request;

class NotificationLogController extends Controller
{
    public function index(Request $request)
    {
        $logs = NotificationLog::with('triggeredBy')
            ->when($request->channel, fn ($q) => $q->where('channel', $request->channel))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->search, fn ($q) => $q->where('recipient', 'like', "%{$request->search}%"))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'total_sent' => NotificationLog::where('status', 'sent')->count(),
            'total_failed' => NotificationLog::where('status', 'failed')->count(),
            'emails' => NotificationLog::where('channel', 'email')->count(),
            'sms' => NotificationLog::where('channel', 'sms')->count(),
        ];

        return view('notification-logs.index', compact('logs', 'stats'));
    }
}
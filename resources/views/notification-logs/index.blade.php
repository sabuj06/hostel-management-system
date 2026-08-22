@extends('layouts.app')

@section('title', 'Notification Logs')
@section('page-title', 'Email & SMS Notification Logs')

@section('content')
<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="card shadow-sm border-0"><div class="card-body">
            <div class="text-muted small">Total Sent</div>
            <div class="fs-4 fw-bold text-success">{{ $stats['total_sent'] }}</div>
        </div></div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card shadow-sm border-0"><div class="card-body">
            <div class="text-muted small">Failed</div>
            <div class="fs-4 fw-bold text-danger">{{ $stats['total_failed'] }}</div>
        </div></div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card shadow-sm border-0"><div class="card-body">
            <div class="text-muted small">Emails</div>
            <div class="fs-4 fw-bold">{{ $stats['emails'] }}</div>
        </div></div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card shadow-sm border-0"><div class="card-body">
            <div class="text-muted small">SMS</div>
            <div class="fs-4 fw-bold">{{ $stats['sms'] }}</div>
        </div></div>
    </div>
</div>

<form method="GET" class="d-flex gap-2 mb-3">
    <input type="text" name="search" class="form-control" placeholder="Search recipient..." value="{{ request('search') }}">
    <select name="channel" class="form-select" onchange="this.form.submit()">
        <option value="">All Channels</option>
        <option value="email" @selected(request('channel') === 'email')>Email</option>
        <option value="sms" @selected(request('channel') === 'sms')>SMS</option>
    </select>
    <select name="status" class="form-select" onchange="this.form.submit()">
        <option value="">All Status</option>
        <option value="sent" @selected(request('status') === 'sent')>Sent</option>
        <option value="failed" @selected(request('status') === 'failed')>Failed</option>
    </select>
    <button class="btn btn-outline-secondary"><i class="bi bi-search"></i></button>
</form>

<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr><th>Channel</th><th>Recipient</th><th>Subject/Message</th><th>Status</th><th>Sent At</th></tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr>
                    <td><span class="badge bg-light text-dark border text-uppercase">{{ $log->channel }}</span></td>
                    <td>{{ $log->recipient }}</td>
                    <td class="small" style="max-width:350px;">
                        @if($log->subject)<strong>{{ $log->subject }}</strong><br>@endif
                        {{ \Illuminate\Support\Str::limit($log->message, 80) }}
                        @if($log->status === 'failed')
                            <div class="text-danger">{{ $log->error }}</div>
                        @endif
                    </td>
                    <td><span class="badge bg-{{ $log->status === 'sent' ? 'success' : 'danger' }}">{{ ucfirst($log->status) }}</span></td>
                    <td>{{ $log->created_at->format('d M Y, h:i A') }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-muted py-4">No notifications sent yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">{{ $logs->links() }}</div>
</div>
@endsection
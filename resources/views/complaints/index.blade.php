@extends('layouts.app')

@section('title', 'Complaints')
@section('page-title', 'Complaints & Maintenance')

@section('content')
<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="text-muted small">Open</div>
                <div class="fs-3 fw-bold text-danger">{{ $counts['open'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="text-muted small">In Progress</div>
                <div class="fs-3 fw-bold text-warning">{{ $counts['in_progress'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="text-muted small">Resolved</div>
                <div class="fs-3 fw-bold text-success">{{ $counts['resolved'] }}</div>
            </div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
    <form method="GET" class="d-flex gap-2">
        <input type="text" name="search" class="form-control" placeholder="Search ticket/title..." value="{{ request('search') }}">
        <select name="status" class="form-select" onchange="this.form.submit()">
            <option value="">All Status</option>
            @foreach(['open','in_progress','resolved','closed','rejected'] as $s)
                <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
            @endforeach
        </select>
        <select name="priority" class="form-select" onchange="this.form.submit()">
            <option value="">All Priority</option>
            @foreach(['low','medium','high','urgent'] as $p)
                <option value="{{ $p }}" @selected(request('priority') === $p)>{{ ucfirst($p) }}</option>
            @endforeach
        </select>
        <button class="btn btn-outline-secondary"><i class="bi bi-search"></i></button>
    </form>
    <div class="d-flex gap-2">
        <a href="{{ route('complaint-categories.index') }}" class="btn btn-outline-secondary"><i class="bi bi-tags"></i> Categories</a>
        <a href="{{ route('complaints.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Log Complaint</a>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Ticket</th>
                    <th>Title</th>
                    <th>Student</th>
                    <th>Category</th>
                    <th>Priority</th>
                    <th>Assigned</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($complaints as $c)
                <tr>
                    <td>{{ $c->ticket_no }}</td>
                    <td>{{ $c->title }}</td>
                    <td>{{ $c->student->name }}</td>
                    <td>{{ $c->category->name ?? '-' }}</td>
                    <td>
                        @php
                            $prBadge = ['low' => 'secondary', 'medium' => 'info', 'high' => 'warning', 'urgent' => 'danger'][$c->priority];
                        @endphp
                        <span class="badge bg-{{ $prBadge }} text-capitalize">{{ $c->priority }}</span>
                    </td>
                    <td>{{ $c->assignedTo->name ?? 'Unassigned' }}</td>
                    <td>
                        @php
                            $stBadge = ['open' => 'danger', 'in_progress' => 'warning', 'resolved' => 'success', 'closed' => 'secondary', 'rejected' => 'dark'][$c->status];
                        @endphp
                        <span class="badge bg-{{ $stBadge }} text-capitalize">{{ str_replace('_',' ',$c->status) }}</span>
                    </td>
                    <td class="text-end">
                        <a href="{{ route('complaints.show', $c) }}" class="btn btn-sm btn-outline-primary">View</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted py-4">No complaints found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">{{ $complaints->links() }}</div>
</div>
@endsection
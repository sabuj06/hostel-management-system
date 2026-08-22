@extends('layouts.student')

@section('title', 'My Complaints')
@section('page-title', 'My Complaints')

@section('content')
<div class="d-flex justify-content-end mb-3">
    <a href="{{ route('student-portal.complaints.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Log New Complaint</a>
</div>

<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Ticket No.</th>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>Submitted</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($complaints as $c)
                <tr>
                    <td>{{ $c->ticket_no }}</td>
                    <td>{{ $c->title }}</td>
                    <td>{{ $c->category->name ?? 'Uncategorized' }}</td>
                    <td><span class="badge bg-{{ $c->priority === 'urgent' ? 'danger' : ($c->priority === 'high' ? 'warning' : 'secondary') }} text-capitalize">{{ $c->priority }}</span></td>
                    <td><span class="badge bg-{{ $c->isClosed() ? 'success' : 'info' }} text-capitalize">{{ str_replace('_', ' ', $c->status) }}</span></td>
                    <td>{{ $c->created_at->format('d M Y') }}</td>
                    <td><a href="{{ route('student-portal.complaints.show', $c) }}" class="btn btn-sm btn-outline-secondary">View</a></td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-4">You haven't logged any complaints yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">{{ $complaints->links() }}</div>
</div>
@endsection
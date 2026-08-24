@extends('layouts.app')

@section('title', 'Manage Notices')
@section('page-title', 'Manage Notices')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <form method="GET" class="d-flex gap-2">
        <select name="status" class="form-select" onchange="this.form.submit()">
            <option value="">All Status</option>
            <option value="draft" @selected(request('status') === 'draft')>Draft</option>
            <option value="published" @selected(request('status') === 'published')>Published</option>
            <option value="archived" @selected(request('status') === 'archived')>Archived</option>
        </select>
    </form>
    <div class="d-flex gap-2">
        <a href="{{ route('notices.index') }}" class="btn btn-outline-secondary"><i class="bi bi-eye"></i> View Board</a>
        <a href="{{ route('notices.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Post Notice</a>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Title</th>
                    <th>Audience</th>
                    <th>Priority</th>
                    <th>Publish Date</th>
                    <th>Expiry</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($notices as $notice)
                <tr>
                    <td>{{ $notice->title }}</td>
                    <td class="text-capitalize">{{ $notice->audience }}</td>
                    <td><span class="badge bg-{{ $notice->priority === 'urgent' ? 'danger' : ($notice->priority === 'important' ? 'warning' : 'secondary') }} text-capitalize">{{ $notice->priority }}</span></td>
                    <td>{{ optional($notice->publish_date)->format('d M Y') ?? '-' }}</td>
                    <td>{{ optional($notice->expiry_date)->format('d M Y') ?? '-' }}</td>
                    <td><span class="badge bg-{{ $notice->status === 'published' ? 'success' : ($notice->status === 'draft' ? 'secondary' : 'dark') }} text-capitalize">{{ $notice->status }}</span></td>
                    <td class="text-end">
                        <a href="{{ route('notices.edit', $notice) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                        <form action="{{ route('notices.destroy', $notice) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this notice?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-4">No notices found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">{{ $notices->links() }}</div>
</div>
@endsection
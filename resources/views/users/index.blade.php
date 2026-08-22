@extends('layouts.app')

@section('title', 'Users')
@section('page-title', 'User Management')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <form method="GET" class="d-flex gap-2">
        <input type="text" name="search" class="form-control" placeholder="Search name/email..." value="{{ request('search') }}">
        <select name="role_id" class="form-select" onchange="this.form.submit()">
            <option value="">All Roles</option>
            @foreach($roles as $r)
                <option value="{{ $r->id }}" @selected(request('role_id') == $r->id)>{{ $r->label }}</option>
            @endforeach
        </select>
        <button class="btn btn-outline-secondary"><i class="bi bi-search"></i></button>
    </form>
    <div class="d-flex gap-2">
        <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary"><i class="bi bi-shield-lock"></i> Manage Roles</a>
        <a href="{{ route('users.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add User</a>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr data-user-id="{{ $user->id }}">
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->phone ?? '-' }}</td>
                    <td><span class="badge bg-light text-dark border">{{ $user->role->label ?? '-' }}</span></td>
                    <td class="status-cell">
                        @if($user->id === auth()->id())
                            <span class="badge bg-success">Active (You)</span>
                        @else
                            <button class="btn btn-sm status-toggle-btn btn-{{ $user->status === 'active' ? 'outline-success' : 'outline-secondary' }}" data-user-id="{{ $user->id }}">
                                {{ ucfirst($user->status) }}
                            </button>
                        @endif
                    </td>
                    <td class="text-end">
                        <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                        @if($user->id !== auth()->id())
                        <form action="{{ route('users.destroy', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this user?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No users found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">{{ $users->links() }}</div>
</div>
@endsection

@push('scripts')
<script>
    $(function () {
        $.ajaxSetup({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
        });

        // Toggle active/inactive via AJAX — button updates instantly
        $(document).on('click', '.status-toggle-btn', function () {
            const $btn = $(this);
            const userId = $btn.data('user-id');

            $btn.prop('disabled', true);

            $.ajax({
                url: `/users/${userId}/toggle-status`,
                method: 'POST',
                success: function (res) {
                    $btn.text(res.status.charAt(0).toUpperCase() + res.status.slice(1));
                    $btn.toggleClass('btn-outline-success', res.status === 'active');
                    $btn.toggleClass('btn-outline-secondary', res.status === 'inactive');
                },
                error: function (xhr) {
                    alert(xhr.responseJSON?.message || 'Failed to update status.');
                },
                complete: function () {
                    $btn.prop('disabled', false);
                }
            });
        });
    });
</script>
@endpush
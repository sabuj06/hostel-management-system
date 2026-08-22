@extends('layouts.app')

@section('title', 'Roles')
@section('page-title', 'Role Management')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary"><i class="bi bi-people"></i> Back to Users</a>
    <a href="{{ route('roles.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add Role</a>
</div>

<div class="row g-3">
    @foreach($roles as $role)
    <div class="col-md-4">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h6 class="mb-1">{{ $role->label }}</h6>
                <div class="text-muted small mb-3">{{ $role->name }}</div>
                <div class="d-flex justify-content-between small text-muted mb-3">
                    <span><i class="bi bi-people"></i> {{ $role->users_count }} users</span>
                    <span><i class="bi bi-key"></i> {{ $role->permissions_count }} permissions</span>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('roles.edit', $role) }}" class="btn btn-sm btn-outline-primary flex-grow-1">
                        <i class="bi bi-shield-check"></i> Manage Permissions
                    </a>
                    @unless(in_array($role->name, ['admin','warden','staff','student']))
                    <form action="{{ route('roles.destroy', $role) }}" method="POST" onsubmit="return confirm('Delete this role?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                    </form>
                    @endunless
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endsection
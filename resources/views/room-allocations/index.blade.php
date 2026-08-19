@extends('layouts.app')

@section('title', 'Room Allocations')
@section('page-title', 'Room Allocation History')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <form method="GET" class="d-flex gap-2">
        <input type="text" name="search" class="form-control" placeholder="Search student..." value="{{ request('search') }}">
        <select name="status" class="form-select" onchange="this.form.submit()">
            <option value="">All Status</option>
            <option value="active" @selected(request('status') === 'active')>Active</option>
            <option value="transferred" @selected(request('status') === 'transferred')>Transferred</option>
            <option value="checked_out" @selected(request('status') === 'checked_out')>Checked Out</option>
        </select>
        <button class="btn btn-outline-secondary"><i class="bi bi-search"></i></button>
    </form>
    <a href="{{ route('room-allocations.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Allocate Room</a>
</div>

<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Student</th>
                    <th>Room / Bed</th>
                    <th>Allocated On</th>
                    <th>Vacated On</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($allocations as $alloc)
                <tr>
                    <td>
                        <a href="{{ route('students.show', $alloc->student) }}">{{ $alloc->student->name }}</a>
                        <div class="small text-muted">{{ $alloc->student->student_uid }}</div>
                    </td>
                    <td>Room {{ $alloc->room->room_number }} / Bed {{ $alloc->bed->bed_number }}</td>
                    <td>{{ $alloc->allocated_date->format('d M Y') }}</td>
                    <td>{{ optional($alloc->vacated_date)->format('d M Y') ?? '-' }}</td>
                    <td>
                        <span class="badge bg-{{ $alloc->status === 'active' ? 'success' : ($alloc->status === 'transferred' ? 'warning' : 'secondary') }} text-capitalize">
                            {{ str_replace('_', ' ', $alloc->status) }}
                        </span>
                    </td>
                    <td class="text-end">
                        @if($alloc->status === 'active')
                        <form action="{{ route('room-allocations.checkout', $alloc) }}" method="POST" class="d-inline" onsubmit="return confirm('Checkout this student?')">
                            @csrf
                            <button class="btn btn-sm btn-outline-danger">Checkout</button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No allocations found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">{{ $allocations->links() }}</div>
</div>
@endsection
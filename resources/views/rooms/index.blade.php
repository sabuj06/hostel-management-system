@extends('layouts.app')

@section('title', 'Rooms')
@section('page-title', 'Room Management')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <form method="GET" class="d-flex gap-2">
        <input type="text" name="search" class="form-control" placeholder="Search room no..." value="{{ request('search') }}">
        <select name="floor_id" class="form-select" onchange="this.form.submit()">
            <option value="">All Floors</option>
            @foreach($floors as $f)
                <option value="{{ $f->id }}" @selected(request('floor_id') == $f->id)>{{ $f->block->name }} - {{ $f->name }}</option>
            @endforeach
        </select>
        <button class="btn btn-outline-secondary"><i class="bi bi-search"></i></button>
    </form>
    <a href="{{ route('rooms.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add Room</a>
</div>

<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Room No.</th>
                    <th>Location</th>
                    <th>Type</th>
                    <th>Capacity</th>
                    <th>Beds</th>
                    <th>Rent</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rooms as $room)
                <tr>
                    <td class="fw-semibold">{{ $room->room_number }}</td>
                    <td class="small text-muted">
                        {{ $room->floor->block->hostel->name }} /
                        {{ $room->floor->block->name }} /
                        {{ $room->floor->name }}
                    </td>
                    <td class="text-capitalize">{{ $room->room_type }}</td>
                    <td>{{ $room->capacity }}</td>
                    <td>{{ $room->beds_count }}</td>
                    <td>₹{{ number_format($room->monthly_rent, 2) }}</td>
                    <td>
                        <span class="badge bg-{{ $room->status === 'active' ? 'success' : ($room->status === 'maintenance' ? 'warning' : 'danger') }}">
                            {{ ucfirst($room->status) }}
                        </span>
                    </td>
                    <td class="text-end">
                        <a href="{{ route('beds.index', ['room_id' => $room->id]) }}" class="btn btn-sm btn-outline-secondary" title="View Beds"><i class="bi bi-grid-3x3-gap"></i></a>
                        <a href="{{ route('rooms.edit', $room) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                        <form action="{{ route('rooms.destroy', $room) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this room and its beds?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted py-4">No rooms found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">{{ $rooms->links() }}</div>
</div>
@endsection
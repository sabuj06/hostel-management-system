@extends('layouts.app')

@section('title', 'Floors')
@section('page-title', 'Floor Management')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <form method="GET" class="d-flex gap-2">
        <select name="block_id" class="form-select" onchange="this.form.submit()">
            <option value="">All Blocks</option>
            @foreach($blocks as $b)
                <option value="{{ $b->id }}" @selected(request('block_id') == $b->id)>{{ $b->hostel->name }} - {{ $b->name }}</option>
            @endforeach
        </select>
    </form>
    <a href="{{ route('floors.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add Floor</a>
</div>

<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Floor</th>
                    <th>Block</th>
                    <th>Hostel</th>
                    <th>Rooms</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($floors as $floor)
                <tr>
                    <td>{{ $floor->name }} <span class="text-muted">(#{{ $floor->floor_number }})</span></td>
                    <td>{{ $floor->block->name }}</td>
                    <td>{{ $floor->block->hostel->name }}</td>
                    <td>{{ $floor->rooms_count }}</td>
                    <td><span class="badge bg-{{ $floor->status === 'active' ? 'success' : 'danger' }}">{{ ucfirst($floor->status) }}</span></td>
                    <td class="text-end">
                        <a href="{{ route('floors.edit', $floor) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                        <form action="{{ route('floors.destroy', $floor) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this floor?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No floors found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">{{ $floors->links() }}</div>
</div>
@endsection
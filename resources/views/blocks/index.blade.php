@extends('layouts.app')

@section('title', 'Blocks')
@section('page-title', 'Block Management')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <form method="GET" class="d-flex gap-2">
        <select name="hostel_id" class="form-select" onchange="this.form.submit()">
            <option value="">All Hostels</option>
            @foreach($hostels as $h)
                <option value="{{ $h->id }}" @selected(request('hostel_id') == $h->id)>{{ $h->name }}</option>
            @endforeach
        </select>
    </form>
    <a href="{{ route('blocks.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add Block</a>
</div>

<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Block</th>
                    <th>Hostel</th>
                    <th>Floors</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($blocks as $block)
                <tr>
                    <td>{{ $block->name }}</td>
                    <td>{{ $block->hostel->name }}</td>
                    <td>{{ $block->floors_count }}</td>
                    <td><span class="badge bg-{{ $block->status === 'active' ? 'success' : 'danger' }}">{{ ucfirst($block->status) }}</span></td>
                    <td class="text-end">
                        <a href="{{ route('blocks.edit', $block) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                        <form action="{{ route('blocks.destroy', $block) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this block?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-muted py-4">No blocks found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">{{ $blocks->links() }}</div>
</div>
@endsection
@extends('layouts.app')

@section('title', 'Hostels')
@section('page-title', 'Hostel Management')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <form method="GET" class="d-flex gap-2">
        <input type="text" name="search" class="form-control" placeholder="Search hostel..." value="{{ request('search') }}">
        <button class="btn btn-outline-secondary"><i class="bi bi-search"></i></button>
    </form>
    <a href="{{ route('hostels.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add Hostel</a>
</div>

<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Warden</th>
                    <th>Blocks</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($hostels as $hostel)
                <tr>
                    <td>{{ $hostel->name }}</td>
                    <td><span class="badge bg-secondary text-capitalize">{{ $hostel->type }}</span></td>
                    <td>{{ $hostel->warden_name ?? '-' }}</td>
                    <td>{{ $hostel->blocks_count }}</td>
                    <td>
                        <span class="badge bg-{{ $hostel->status === 'active' ? 'success' : 'danger' }}">
                            {{ ucfirst($hostel->status) }}
                        </span>
                    </td>
                    <td class="text-end">
                        <a href="{{ route('hostels.edit', $hostel) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                        <form action="{{ route('hostels.destroy', $hostel) }}" method="POST" class="d-inline delete-form">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No hostels found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">{{ $hostels->links() }}</div>
</div>
@endsection

@push('scripts')
<script>
    $(function () {
        $('.delete-form').on('submit', function (e) {
            if (!confirm('Delete this hostel? This cannot be undone easily.')) {
                e.preventDefault();
            }
        });
    });
</script>
@endpush
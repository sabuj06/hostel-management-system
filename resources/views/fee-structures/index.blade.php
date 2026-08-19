@extends('layouts.app')

@section('title', 'Fee Structures')
@section('page-title', 'Fee Structure Management')

@section('content')
<div class="d-flex justify-content-end mb-3">
    <a href="{{ route('fee-structures.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add Fee Structure</a>
</div>

<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Name</th>
                    <th>Hostel</th>
                    <th>Room Type</th>
                    <th>Amount</th>
                    <th>Frequency</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($feeStructures as $fee)
                <tr>
                    <td>{{ $fee->name }}</td>
                    <td>{{ $fee->hostel->name ?? 'All Hostels' }}</td>
                    <td class="text-capitalize">{{ $fee->room_type }}</td>
                    <td>৳{{ number_format($fee->amount, 2) }}</td>
                    <td class="text-capitalize">{{ str_replace('_', ' ', $fee->frequency) }}</td>
                    <td><span class="badge bg-{{ $fee->status === 'active' ? 'success' : 'secondary' }}">{{ ucfirst($fee->status) }}</span></td>
                    <td class="text-end">
                        <a href="{{ route('fee-structures.edit', $fee) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                        <form action="{{ route('fee-structures.destroy', $fee) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this fee structure?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-4">No fee structures found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">{{ $feeStructures->links() }}</div>
</div>
@endsection
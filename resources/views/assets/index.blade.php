@extends('layouts.app')

@section('title', 'Assets & Inventory')
@section('page-title', 'Assets & Inventory')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-box-seam me-2"></i>Assets & Inventory</h4>
    <div class="d-flex gap-2">
        <a href="{{ route('asset-damage-reports.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-tools"></i> Damage Reports
        </a>
        <a href="{{ route('assets.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Add Asset
        </a>
    </div>
</div>

@if(session('status'))
    <div class="alert alert-success">{{ session('status') }}</div>
@endif

@if($lowStockAssets->isNotEmpty())
<div class="alert alert-warning">
    <i class="bi bi-exclamation-triangle me-1"></i> <strong>Low stock alert:</strong>
    {{ $lowStockAssets->pluck('name')->join(', ') }}
</div>
@endif

<form method="GET" class="d-flex gap-2 mb-3">
    <input type="text" name="search" class="form-control" style="max-width:250px;" placeholder="Search asset..." value="{{ request('search') }}">
    <select name="category_id" class="form-select" style="max-width:200px;">
        <option value="">All Categories</option>
        @foreach($categories as $cat)
            <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
        @endforeach
    </select>
    <button class="btn btn-outline-secondary"><i class="bi bi-search"></i></button>
</form>

<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Total</th>
                    <th>Available</th>
                    <th>Status</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($assets as $asset)
                <tr>
                    <td>
                        {{ $asset->name }}
                        @if($asset->isLowStock())
                            <span class="badge bg-warning text-dark ms-1">Low Stock</span>
                        @endif
                        <div class="small text-muted">{{ $asset->sku ?? '-' }}</div>
                    </td>
                    <td>{{ $asset->category->name ?? '-' }}</td>
                    <td>{{ $asset->quantity_total }}</td>
                    <td>{{ $asset->quantity_available }}</td>
                    <td>
                        @php
                            $badge = ['active' => 'success', 'damaged' => 'warning', 'written_off' => 'danger'][$asset->status];
                        @endphp
                        <span class="badge bg-{{ $badge }} text-capitalize">{{ str_replace('_', ' ', $asset->status) }}</span>
                    </td>
                    <td class="text-end">
                        @if($asset->status === 'active' && $asset->quantity_available > 0)
                        <a href="{{ route('assets.assign-form', $asset) }}" class="btn btn-sm btn-outline-primary">Assign to Room</a>
                        @endif
                        @if($asset->status !== 'written_off')
                        <form method="POST" action="{{ route('assets.write-off', $asset) }}" class="d-inline"
                              onsubmit="return confirm('Mark this asset as written off?');">
                            @csrf
                            <button class="btn btn-sm btn-outline-danger">Write Off</button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No assets added yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">{{ $assets->links() }}</div>
</div>
@endsection
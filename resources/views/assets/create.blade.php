@extends('layouts.app')

@section('title', 'Add Asset')
@section('page-title', 'Add Asset')

@section('content')
<div class="card shadow-sm border-0" style="max-width:600px;">
    <div class="card-body">
        <form method="POST" action="{{ route('assets.store') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label">Category</label>
                <select name="asset_category_id" class="form-select @error('asset_category_id') is-invalid @enderror">
                    <option value="">Select category</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('asset_category_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }} ({{ $cat->type }})
                        </option>
                    @endforeach
                </select>
                @error('asset_category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Asset Name</label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}">
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label">SKU (optional)</label>
                <input type="text" name="sku" class="form-control @error('sku') is-invalid @enderror" value="{{ old('sku') }}">
                @error('sku')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Total Quantity</label>
                    <input type="number" name="quantity_total" min="1" class="form-control @error('quantity_total') is-invalid @enderror" value="{{ old('quantity_total', 1) }}">
                    @error('quantity_total')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Low Stock Threshold</label>
                    <input type="number" name="low_stock_threshold" min="0" class="form-control" value="{{ old('low_stock_threshold', 0) }}">
                    <div class="form-text">Set 0 to disable low-stock alerts for this item.</div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Unit Cost</label>
                    <input type="number" step="0.01" name="unit_cost" class="form-control" value="{{ old('unit_cost') }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Purchase Date</label>
                    <input type="date" name="purchase_date" class="form-control" value="{{ old('purchase_date') }}">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Warranty Expiry (optional)</label>
                <input type="date" name="warranty_expiry" class="form-control" value="{{ old('warranty_expiry') }}">
            </div>

            <div class="mb-3">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
            </div>

            <div class="d-flex gap-2">
                <button class="btn btn-primary">Save Asset</button>
                <a href="{{ route('assets.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
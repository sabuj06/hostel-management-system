@extends('layouts.app')

@section('title', 'Assign Asset')
@section('page-title', 'Assign Asset to Room')

@section('content')
<div class="card shadow-sm border-0" style="max-width:600px;">
    <div class="card-body">
        <h6 class="mb-3">{{ $asset->name }} <span class="text-muted small">(Available: {{ $asset->quantity_available }})</span></h6>

        <form method="POST" action="{{ route('assets.assign-store', $asset) }}">
            @csrf

            <div class="mb-3">
                <label class="form-label">Room</label>
                <select name="room_id" class="form-select @error('room_id') is-invalid @enderror">
                    <option value="">Select room</option>
                    @foreach($rooms as $room)
                        <option value="{{ $room->id }}" {{ old('room_id') == $room->id ? 'selected' : '' }}>{{ $room->room_number }}</option>
                    @endforeach
                </select>
                @error('room_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Quantity</label>
                <input type="number" name="quantity" min="1" max="{{ $asset->quantity_available }}"
                       class="form-control @error('quantity') is-invalid @enderror" value="{{ old('quantity', 1) }}">
                @error('quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Assigned Date</label>
                <input type="date" name="assigned_date" class="form-control" value="{{ old('assigned_date', now()->format('Y-m-d')) }}">
            </div>

            <div class="mb-3">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
            </div>

            <div class="d-flex gap-2">
                <button class="btn btn-primary">Assign</button>
                <a href="{{ route('assets.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
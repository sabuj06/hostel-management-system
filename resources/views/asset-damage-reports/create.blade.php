@extends('layouts.app')

@section('title', 'Report Damage')
@section('page-title', 'Report Asset Damage')

@section('content')
<div class="card shadow-sm border-0" style="max-width:600px;">
    <div class="card-body">
        <form method="POST" action="{{ route('asset-damage-reports.store') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label">Asset</label>
                <select name="asset_id" class="form-select @error('asset_id') is-invalid @enderror">
                    <option value="">Select asset</option>
                    @foreach($assets as $asset)
                        <option value="{{ $asset->id }}" {{ old('asset_id') == $asset->id ? 'selected' : '' }}>{{ $asset->name }}</option>
                    @endforeach
                </select>
                @error('asset_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Room (optional)</label>
                <select name="room_id" class="form-select">
                    <option value="">Not room-specific</option>
                    @foreach($rooms as $room)
                        <option value="{{ $room->id }}" {{ old('room_id') == $room->id ? 'selected' : '' }}>{{ $room->room_number }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description') }}</textarea>
                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="d-flex gap-2">
                <button class="btn btn-primary">Submit Report</button>
                <a href="{{ route('asset-damage-reports.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
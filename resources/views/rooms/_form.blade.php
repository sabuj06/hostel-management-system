@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="mb-3">
    <label class="form-label">Floor</label>
    <select name="floor_id" class="form-select" required>
        <option value="">Select Floor</option>
        @foreach($floors as $f)
            <option value="{{ $f->id }}" @selected(old('floor_id', $room->floor_id ?? '') == $f->id)>
                {{ $f->block->hostel->name }} / {{ $f->block->name }} / {{ $f->name }}
            </option>
        @endforeach
    </select>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Room Number</label>
        <input type="text" name="room_number" class="form-control" value="{{ old('room_number', $room->room_number ?? '') }}" required>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Room Type</label>
        <select name="room_type" class="form-select" required>
            @foreach(['single' => 'Single', 'double' => 'Double', 'triple' => 'Triple', 'dormitory' => 'Dormitory'] as $val => $label)
                <option value="{{ $val }}" @selected(old('room_type', $room->room_type ?? '') === $val)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">
            Capacity (beds)
            @if(isset($room))
                <span class="text-muted small">— editing capacity will NOT auto-add/remove beds; manage via Beds page</span>
            @endif
        </label>
        <input type="number" name="capacity" class="form-control" min="1" max="20" value="{{ old('capacity', $room->capacity ?? 1) }}" required>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Monthly Rent</label>
        <input type="number" step="0.01" name="monthly_rent" class="form-control" value="{{ old('monthly_rent', $room->monthly_rent ?? 0) }}" required>
    </div>
</div>

<div class="mb-3">
    <label class="form-label">Status</label>
    <select name="status" class="form-select" required>
        <option value="active" @selected(old('status', $room->status ?? 'active') === 'active')>Active</option>
        <option value="maintenance" @selected(old('status', $room->status ?? '') === 'maintenance')>Maintenance</option>
        <option value="inactive" @selected(old('status', $room->status ?? '') === 'inactive')>Inactive</option>
    </select>
</div>

@if(!isset($room))
<div class="alert alert-info small">
    <i class="bi bi-info-circle"></i> Saving this form will auto-create bed records equal to Capacity.
</div>
@endif
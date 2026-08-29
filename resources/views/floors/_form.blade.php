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
    <label class="form-label">Block</label>
    <select name="block_id" class="form-select" required>
        <option value="">Select Block</option>
        @foreach($blocks as $b)
            <option value="{{ $b->id }}" @selected(old('block_id', $floor->block_id ?? '') == $b->id)>
                {{ $b->hostel->name }} - {{ $b->name }}
            </option>
        @endforeach
    </select>
</div>

<div class="row">
    <div class="col-md-8 mb-3">
        <label class="form-label">Floor Name</label>
        <input type="text" name="name" class="form-control" placeholder="e.g. 1st Floor" value="{{ old('name', $floor->name ?? '') }}" required>
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Floor Number</label>
        <input type="number" name="floor_number" class="form-control" min="0" value="{{ old('floor_number', $floor->floor_number ?? 0) }}" required>
    </div>
</div>

<div class="mb-3">
    <label class="form-label">Status</label>
    <select name="status" class="form-select" required>
        <option value="active" @selected(old('status', $floor->status ?? 'active') === 'active')>Active</option>
        <option value="inactive" @selected(old('status', $floor->status ?? '') === 'inactive')>Inactive</option>
    </select>
</div>
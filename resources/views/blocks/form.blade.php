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
    <label class="form-label">Hostel</label>
    <select name="hostel_id" class="form-select" required>
        <option value="">Select Hostel</option>
        @foreach($hostels as $h)
            <option value="{{ $h->id }}" @selected(old('hostel_id', $block->hostel_id ?? '') == $h->id)>{{ $h->name }}</option>
        @endforeach
    </select>
</div>

<div class="mb-3">
    <label class="form-label">Block Name</label>
    <input type="text" name="name" class="form-control" placeholder="e.g. Block A" value="{{ old('name', $block->name ?? '') }}" required>
</div>

<div class="mb-3">
    <label class="form-label">Description</label>
    <textarea name="description" class="form-control" rows="2">{{ old('description', $block->description ?? '') }}</textarea>
</div>

<div class="mb-3">
    <label class="form-label">Status</label>
    <select name="status" class="form-select" required>
        <option value="active" @selected(old('status', $block->status ?? 'active') === 'active')>Active</option>
        <option value="inactive" @selected(old('status', $block->status ?? '') === 'inactive')>Inactive</option>
    </select>
</div>
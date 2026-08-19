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
    <label class="form-label">Hostel Name</label>
    <input type="text" name="name" class="form-control" value="{{ old('name', $hostel->name ?? '') }}" required>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Type</label>
        <select name="type" class="form-select" required>
            @foreach(['boys' => 'Boys', 'girls' => 'Girls', 'mixed' => 'Mixed'] as $val => $label)
                <option value="{{ $val }}" @selected(old('type', $hostel->type ?? '') === $val)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Status</label>
        <select name="status" class="form-select" required>
            <option value="active" @selected(old('status', $hostel->status ?? 'active') === 'active')>Active</option>
            <option value="inactive" @selected(old('status', $hostel->status ?? '') === 'inactive')>Inactive</option>
        </select>
    </div>
</div>

<div class="mb-3">
    <label class="form-label">Address</label>
    <textarea name="address" class="form-control" rows="2">{{ old('address', $hostel->address ?? '') }}</textarea>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Warden Name</label>
        <input type="text" name="warden_name" class="form-control" value="{{ old('warden_name', $hostel->warden_name ?? '') }}">
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Contact Number</label>
        <input type="text" name="contact_number" class="form-control" value="{{ old('contact_number', $hostel->contact_number ?? '') }}">
    </div>
</div>
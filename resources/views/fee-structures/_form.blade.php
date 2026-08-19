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
    <label class="form-label">Fee Name</label>
    <input type="text" name="name" class="form-control" placeholder="e.g. Monthly Hostel Fee - Double Room" value="{{ old('name', $feeStructure->name ?? '') }}" required>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Hostel (optional)</label>
        <select name="hostel_id" class="form-select">
            <option value="">All Hostels</option>
            @foreach($hostels as $h)
                <option value="{{ $h->id }}" @selected(old('hostel_id', $feeStructure->hostel_id ?? '') == $h->id)>{{ $h->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Room Type</label>
        <select name="room_type" class="form-select" required>
            @foreach(['any' => 'Any', 'single' => 'Single', 'double' => 'Double', 'triple' => 'Triple', 'dormitory' => 'Dormitory'] as $val => $label)
                <option value="{{ $val }}" @selected(old('room_type', $feeStructure->room_type ?? 'any') === $val)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Amount</label>
        <input type="number" step="0.01" name="amount" class="form-control" value="{{ old('amount', $feeStructure->amount ?? 0) }}" required>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Frequency</label>
        <select name="frequency" class="form-select" required>
            <option value="monthly" @selected(old('frequency', $feeStructure->frequency ?? 'monthly') === 'monthly')>Monthly</option>
            <option value="yearly" @selected(old('frequency', $feeStructure->frequency ?? '') === 'yearly')>Yearly</option>
            <option value="one_time" @selected(old('frequency', $feeStructure->frequency ?? '') === 'one_time')>One Time</option>
        </select>
    </div>
</div>

<div class="mb-3">
    <label class="form-label">Description</label>
    <textarea name="description" class="form-control" rows="2">{{ old('description', $feeStructure->description ?? '') }}</textarea>
</div>

<div class="mb-3">
    <label class="form-label">Status</label>
    <select name="status" class="form-select" required>
        <option value="active" @selected(old('status', $feeStructure->status ?? 'active') === 'active')>Active</option>
        <option value="inactive" @selected(old('status', $feeStructure->status ?? '') === 'inactive')>Inactive</option>
    </select>
</div>
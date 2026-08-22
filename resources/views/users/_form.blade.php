@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Full Name</label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $user->name ?? '') }}" required>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" value="{{ old('email', $user->email ?? '') }}" required>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Phone</label>
        <input type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone ?? '') }}">
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Role</label>
        <select name="role_id" class="form-select" required>
            <option value="">Select Role</option>
            @foreach($roles as $r)
                <option value="{{ $r->id }}" @selected(old('role_id', $user->role_id ?? '') == $r->id)>{{ $r->label }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Password {{ isset($user) ? '(leave blank to keep unchanged)' : '' }}</label>
        <input type="password" name="password" class="form-control" {{ isset($user) ? '' : 'required' }}>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Confirm Password</label>
        <input type="password" name="password_confirmation" class="form-control" {{ isset($user) ? '' : 'required' }}>
    </div>
</div>

<div class="mb-3">
    <label class="form-label">Status</label>
    <select name="status" class="form-select" required>
        <option value="active" @selected(old('status', $user->status ?? 'active') === 'active')>Active</option>
        <option value="inactive" @selected(old('status', $user->status ?? '') === 'inactive')>Inactive</option>
    </select>
</div>
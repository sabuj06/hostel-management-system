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
        <label class="form-label">Student UID {{ isset($student) ? '' : '(leave blank to auto-generate)' }}</label>
        <input type="text" name="student_uid" class="form-control" value="{{ old('student_uid', $student->student_uid ?? '') }}" {{ isset($student) ? 'required' : '' }}>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Full Name</label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $student->name ?? '') }}" required>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" value="{{ old('email', $student->email ?? '') }}">
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Phone</label>
        <input type="text" name="phone" class="form-control" value="{{ old('phone', $student->phone ?? '') }}">
    </div>
</div>

<div class="row">
    <div class="col-md-4 mb-3">
        <label class="form-label">Gender</label>
        <select name="gender" class="form-select">
            <option value="">Select</option>
            @foreach(['male' => 'Male', 'female' => 'Female', 'other' => 'Other'] as $val => $label)
                <option value="{{ $val }}" @selected(old('gender', $student->gender ?? '') === $val)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Date of Birth</label>
        <input type="date" name="date_of_birth" class="form-control" value="{{ old('date_of_birth', optional($student->date_of_birth ?? null)->format('Y-m-d')) }}">
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Admission Date</label>
        <input type="date" name="admission_date" class="form-control" value="{{ old('admission_date', optional($student->admission_date ?? null)->format('Y-m-d')) }}">
    </div>
</div>

<div class="row">
    <div class="col-md-4 mb-3">
        <label class="form-label">Course</label>
        <input type="text" name="course" class="form-control" value="{{ old('course', $student->course ?? '') }}">
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Department</label>
        <input type="text" name="department" class="form-control" value="{{ old('department', $student->department ?? '') }}">
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Session</label>
        <input type="text" name="session" class="form-control" placeholder="e.g. 2024-2025" value="{{ old('session', $student->session ?? '') }}">
    </div>
</div>

<div class="mb-3">
    <label class="form-label">Address</label>
    <textarea name="address" class="form-control" rows="2">{{ old('address', $student->address ?? '') }}</textarea>
</div>

<div class="mb-3">
    <label class="form-label">Status</label>
    <select name="status" class="form-select" required>
        <option value="active" @selected(old('status', $student->status ?? 'active') === 'active')>Active</option>
        <option value="inactive" @selected(old('status', $student->status ?? '') === 'inactive')>Inactive</option>
        <option value="left" @selected(old('status', $student->status ?? '') === 'left')>Left</option>
    </select>
</div>
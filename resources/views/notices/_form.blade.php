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
    <label class="form-label">Title</label>
    <input type="text" name="title" class="form-control" value="{{ old('title', $notice->title ?? '') }}" required>
</div>

<div class="mb-3">
    <label class="form-label">Body</label>
    <textarea name="body" class="form-control" rows="5" required>{{ old('body', $notice->body ?? '') }}</textarea>
</div>

<div class="row">
    <div class="col-md-4 mb-3">
        <label class="form-label">Audience</label>
        <select name="audience" id="audience" class="form-select" required>
            @foreach(['all' => 'Everyone', 'students' => 'Students Only', 'staff' => 'Staff Only', 'hostel' => 'Specific Hostel'] as $val => $label)
                <option value="{{ $val }}" @selected(old('audience', $notice->audience ?? 'all') === $val)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4 mb-3" id="hostel-field" style="{{ old('audience', $notice->audience ?? '') === 'hostel' ? '' : 'display:none;' }}">
        <label class="form-label">Hostel</label>
        <select name="hostel_id" class="form-select">
            <option value="">Select Hostel</option>
            @foreach($hostels as $h)
                <option value="{{ $h->id }}" @selected(old('hostel_id', $notice->hostel_id ?? '') == $h->id)>{{ $h->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Priority</label>
        <select name="priority" class="form-select" required>
            <option value="normal" @selected(old('priority', $notice->priority ?? 'normal') === 'normal')>Normal</option>
            <option value="important" @selected(old('priority', $notice->priority ?? '') === 'important')>Important</option>
            <option value="urgent" @selected(old('priority', $notice->priority ?? '') === 'urgent')>Urgent</option>
        </select>
    </div>
</div>

<div class="row">
    <div class="col-md-4 mb-3">
        <label class="form-label">Publish Date</label>
        <input type="date" name="publish_date" class="form-control" value="{{ old('publish_date', optional($notice->publish_date ?? null)->format('Y-m-d') ?? now()->format('Y-m-d')) }}">
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Expiry Date (optional)</label>
        <input type="date" name="expiry_date" class="form-control" value="{{ old('expiry_date', optional($notice->expiry_date ?? null)->format('Y-m-d')) }}">
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Status</label>
        <select name="status" class="form-select" required>
            <option value="published" @selected(old('status', $notice->status ?? 'published') === 'published')>Published</option>
            <option value="draft" @selected(old('status', $notice->status ?? '') === 'draft')>Draft</option>
            <option value="archived" @selected(old('status', $notice->status ?? '') === 'archived')>Archived</option>
        </select>
    </div>
</div>

@unless(isset($notice))
<div class="form-check mb-3">
    <input type="checkbox" class="form-check-input" name="notify_now" id="notify_now" value="1" checked>
    <label class="form-check-label" for="notify_now">
        Notify students immediately via Email/SMS when published
    </label>
</div>
@endunless
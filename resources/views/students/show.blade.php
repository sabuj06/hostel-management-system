@extends('layouts.app')

@section('title', $student->name)
@section('page-title', 'Student Profile')

@section('content')
<div class="row g-3">
    <div class="col-md-4">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h5 class="mb-1">{{ $student->name }}</h5>
                <div class="text-muted small mb-3">{{ $student->student_uid }}</div>
                <ul class="list-unstyled small mb-0">
                    <li class="mb-2"><i class="bi bi-envelope me-2"></i>{{ $student->email ?? '-' }}</li>
                    <li class="mb-2"><i class="bi bi-telephone me-2"></i>{{ $student->phone ?? '-' }}</li>
                    <li class="mb-2"><i class="bi bi-mortarboard me-2"></i>{{ $student->course }} / {{ $student->department }}</li>
                    <li class="mb-2"><i class="bi bi-calendar me-2"></i>Admitted: {{ optional($student->admission_date)->format('d M Y') ?? '-' }}</li>
                    <li><i class="bi bi-flag me-2"></i>
                        <span class="badge bg-{{ $student->status === 'active' ? 'success' : 'secondary' }}">{{ ucfirst($student->status) }}</span>
                    </li>
                    <li class="mt-2"><i class="bi bi-shield-lock me-2"></i>
                        @if($student->user)
                            <span class="badge bg-success">Login Linked</span>
                            <span class="text-muted small">{{ $student->user->email }}</span>
                        @else
                            <span class="badge bg-warning text-dark">No Login Linked</span>
                        @endif
                    </li>
                </ul>
                <a href="{{ route('students.edit', $student) }}" class="btn btn-sm btn-outline-primary mt-3">Edit Profile</a>
                <a href="{{ route('students.id-card', $student) }}" class="btn btn-sm btn-outline-secondary mt-3">
                    <i class="bi bi-qr-code"></i> ID Card
                </a>
            </div>
        </div>

        <!-- Current allocation -->
        <div class="card shadow-sm border-0 mt-3">
            <div class="card-body">
                <h6 class="mb-3">Current Room</h6>
                @php $current = $student->allocations->firstWhere('status', 'active'); @endphp
                @if($current)
                    <div class="mb-2">Room <strong>{{ $current->room->room_number }}</strong>, Bed <strong>{{ $current->bed->bed_number }}</strong></div>
                    <div class="small text-muted mb-3">Since {{ $current->allocated_date->format('d M Y') }}</div>
                    <form action="{{ route('room-allocations.checkout', $current) }}" method="POST" onsubmit="return confirm('Checkout this student?')">
                        @csrf
                        <button class="btn btn-sm btn-outline-danger">Checkout</button>
                    </form>
                @else
                    <p class="text-muted small mb-2">Not allocated to any room.</p>
                    <a href="{{ route('room-allocations.create') }}" class="btn btn-sm btn-primary">Allocate Room</a>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <!-- Guardians -->
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0">Guardians</h6>
                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#guardianModal">
                        <i class="bi bi-plus-lg"></i> Add Guardian
                    </button>
                </div>

                <div id="guardian-list">
                    @foreach($student->guardians as $guardian)
                    <div class="d-flex justify-content-between align-items-center border rounded p-2 mb-2" data-guardian-id="{{ $guardian->id }}">
                        <div>
                            <div class="fw-semibold">
                                {{ $guardian->name }}
                                <span class="badge bg-light text-dark text-capitalize border">{{ $guardian->relation }}</span>
                                @if($guardian->is_primary)<span class="badge bg-primary">Primary</span>@endif
                            </div>
                            <div class="small text-muted">{{ $guardian->phone }} @if($guardian->email) &middot; {{ $guardian->email }} @endif</div>
                        </div>
                        <button class="btn btn-sm btn-outline-danger delete-guardian" data-guardian-id="{{ $guardian->id }}">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                    @endforeach
                    @if($student->guardians->isEmpty())
                        <p class="text-muted small" id="no-guardians">No guardian added yet.</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Allocation history -->
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h6 class="mb-3">Room Allocation History</h6>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr><th>Room</th><th>Bed</th><th>From</th><th>To</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                            @forelse($student->allocations->sortByDesc('allocated_date') as $alloc)
                            <tr>
                                <td>{{ $alloc->room->room_number }}</td>
                                <td>{{ $alloc->bed->bed_number }}</td>
                                <td>{{ $alloc->allocated_date->format('d M Y') }}</td>
                                <td>{{ optional($alloc->vacated_date)->format('d M Y') ?? '-' }}</td>
                                <td><span class="badge bg-secondary text-capitalize">{{ str_replace('_', ' ', $alloc->status) }}</span></td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center text-muted py-3">No allocation history.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Guardian Modal -->
<div class="modal fade" id="guardianModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="guardianForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add Guardian</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-2">
                            <label class="form-label">Relation</label>
                            <select name="relation" class="form-select">
                                @foreach(['father','mother','brother','sister','uncle','other'] as $rel)
                                    <option value="{{ $rel }}">{{ ucfirst($rel) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 mb-2">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Occupation</label>
                        <input type="text" name="occupation" class="form-control">
                    </div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" name="is_primary" id="is_primary" value="1">
                        <label class="form-check-label" for="is_primary">Set as primary guardian</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save Guardian</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(function () {
        $.ajaxSetup({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
        });

        // Add guardian via AJAX, no page reload
        $('#guardianForm').on('submit', function (e) {
            e.preventDefault();
            const $form = $(this);
            const $btn = $form.find('button[type=submit]').prop('disabled', true).text('Saving...');

            $.ajax({
                url: '{{ route('guardians.store', $student) }}',
                method: 'POST',
                data: $form.serialize(),
                success: function (res) {
                    $('#no-guardians').remove();
                    let html = '';
                    res.guardians.forEach(function (g) {
                        html += `<div class="d-flex justify-content-between align-items-center border rounded p-2 mb-2" data-guardian-id="${g.id}">
                            <div>
                                <div class="fw-semibold">${g.name}
                                    <span class="badge bg-light text-dark text-capitalize border">${g.relation}</span>
                                    ${g.is_primary ? '<span class="badge bg-primary">Primary</span>' : ''}
                                </div>
                                <div class="small text-muted">${g.phone}${g.email ? ' &middot; ' + g.email : ''}</div>
                            </div>
                            <button class="btn btn-sm btn-outline-danger delete-guardian" data-guardian-id="${g.id}"><i class="bi bi-trash"></i></button>
                        </div>`;
                    });
                    $('#guardian-list').html(html);
                    $('#guardianModal').modal('hide');
                    $form[0].reset();
                },
                error: function (xhr) {
                    alert('Failed to save guardian. Please check the form.');
                },
                complete: function () {
                    $btn.prop('disabled', false).text('Save Guardian');
                }
            });
        });

        // Delete guardian via AJAX
        $(document).on('click', '.delete-guardian', function () {
            if (!confirm('Remove this guardian?')) return;
            const $row = $(this).closest('[data-guardian-id]');
            const id = $(this).data('guardian-id');

            $.ajax({
                url: `/guardians/${id}`,
                method: 'DELETE',
                success: function () {
                    $row.fadeOut(200, () => $row.remove());
                },
                error: function () {
                    alert('Failed to remove guardian.');
                }
            });
        });
    });
</script>
@endpush
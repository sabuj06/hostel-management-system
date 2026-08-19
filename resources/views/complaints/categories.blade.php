@extends('layouts.app')

@section('title', 'Complaint Categories')
@section('page-title', 'Complaint Categories')

@section('content')
<div class="row">
    <div class="col-md-5">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h6 class="mb-3">Add Category</h6>
                <form id="categoryForm">
                    @csrf
                    <div class="input-group">
                        <input type="text" name="name" class="form-control" placeholder="e.g. Electrical" required>
                        <button class="btn btn-primary" type="submit">Add</button>
                    </div>
                    <div id="category-error" class="text-danger small mt-2" style="display:none;"></div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-7">
        <div class="card shadow-sm border-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>Name</th><th>Complaints</th><th class="text-end">Actions</th></tr>
                    </thead>
                    <tbody id="category-tbody">
                        @forelse($categories as $cat)
                        <tr data-category-id="{{ $cat->id }}">
                            <td>{{ $cat->name }}</td>
                            <td>{{ $cat->complaints_count }}</td>
                            <td class="text-end">
                                <form action="{{ route('complaint-categories.destroy', $cat) }}" method="POST" onsubmit="return confirm('Delete this category?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr id="no-categories"><td colspan="3" class="text-center text-muted py-4">No categories yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
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

        $('#categoryForm').on('submit', function (e) {
            e.preventDefault();
            const $form = $(this);
            $('#category-error').hide();

            $.ajax({
                url: '{{ route('complaint-categories.store') }}',
                method: 'POST',
                data: $form.serialize(),
                success: function (res) {
                    $('#no-categories').remove();
                    $('#category-tbody').append(`
                        <tr data-category-id="${res.category.id}">
                            <td>${res.category.name}</td>
                            <td>0</td>
                            <td class="text-end text-muted small">reload to delete</td>
                        </tr>
                    `);
                    $form[0].reset();
                },
                error: function (xhr) {
                    const msg = xhr.responseJSON?.errors?.name?.[0] || 'Failed to add category.';
                    $('#category-error').text(msg).show();
                }
            });
        });
    });
</script>
@endpush
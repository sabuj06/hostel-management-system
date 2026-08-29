@extends('layouts.app')

@section('title', 'Asset Categories')
@section('page-title', 'Asset Categories')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Asset Categories</h4>
        <p class="text-muted mb-0">
            Manage inventory asset categories.
        </p>
    </div>

    <a href="{{ route('asset-categories.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i>
        Add Category
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success">
        <i class="bi bi-check-circle me-1"></i>
        {{ session('success') }}
    </div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-body">

        @if($categories->count())

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">

                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Category Name</th>
                            <th>Type</th>
                            <th>Assets</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>

                    <tbody>

                        @foreach($categories as $category)

                            <tr>
                                <td>
                                    {{ $loop->iteration }}
                                </td>

                                <td>
                                    <strong>
                                        {{ $category->name }}
                                    </strong>
                                </td>

                                <td>

                                    @if($category->type === 'durable')

                                        <span class="badge bg-primary">
                                            Durable
                                        </span>

                                    @else

                                        <span class="badge bg-success">
                                            Consumable
                                        </span>

                                    @endif

                                </td>

                                <td>
                                    <span class="badge bg-secondary">
                                        {{ $category->assets_count ?? 0 }}
                                    </span>
                                </td>

                                <td class="text-end">

                                    <a href="{{ route('asset-categories.edit', $category) }}"
                                       class="btn btn-sm btn-outline-primary">

                                        <i class="bi bi-pencil"></i>
                                        Edit

                                    </a>

                                    <form action="{{ route('asset-categories.destroy', $category) }}"
                                          method="POST"
                                          class="d-inline">

                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                                class="btn btn-sm btn-outline-danger"
                                                onclick="return confirm('Are you sure you want to delete this category?')">

                                            <i class="bi bi-trash"></i>
                                            Delete

                                        </button>

                                    </form>

                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>
            </div>

        @else

            <div class="text-center py-5">

                <i class="bi bi-tags fs-1 text-muted"></i>

                <h5 class="mt-3">
                    No categories found
                </h5>

                <p class="text-muted">
                    Create your first asset category.
                </p>

                <a href="{{ route('asset-categories.create') }}"
                   class="btn btn-primary">

                    <i class="bi bi-plus-circle me-1"></i>
                    Add Category

                </a>

            </div>

        @endif

    </div>
</div>

@endsection
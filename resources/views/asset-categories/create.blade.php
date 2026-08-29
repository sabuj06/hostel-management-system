@extends('layouts.app')

@section('title', 'Add Asset Category')
@section('page-title', 'Add Asset Category')

@section('content')

<div class="row justify-content-center">

    <div class="col-lg-7">

        <div class="card border-0 shadow-sm">

            <div class="card-header bg-white py-3">
                <h5 class="mb-0">
                    <i class="bi bi-tags me-2"></i>
                    Create Asset Category
                </h5>
            </div>

            <div class="card-body">

                @if($errors->any())

                    <div class="alert alert-danger">

                        <strong>Please fix the following errors:</strong>

                        <ul class="mb-0 mt-2">

                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach

                        </ul>

                    </div>

                @endif

                <form action="{{ route('asset-categories.store') }}"
                      method="POST">

                    @csrf

                    <div class="mb-3">

                        <label for="name" class="form-label">
                            Category Name
                            <span class="text-danger">*</span>
                        </label>

                        <input type="text"
                               name="name"
                               id="name"
                               class="form-control"
                               value="{{ old('name') }}"
                               placeholder="e.g. Furniture"
                               required>

                    </div>

                    <div class="mb-3">

                        <label for="type" class="form-label">
                            Category Type
                            <span class="text-danger">*</span>
                        </label>

                        <select name="type"
                                id="type"
                                class="form-select"
                                required>

                            <option value="">
                                Select type
                            </option>

                            <option value="durable"
                                {{ old('type') === 'durable' ? 'selected' : '' }}>
                                Durable
                            </option>

                            <option value="consumable"
                                {{ old('type') === 'consumable' ? 'selected' : '' }}>
                                Consumable
                            </option>

                        </select>

                        <div class="form-text">

                            <strong>Durable:</strong>
                            Furniture, beds, tables, chairs, appliances etc.

                            <br>

                            <strong>Consumable:</strong>
                            Cleaning supplies, stationery, toiletries etc.

                        </div>

                    </div>

                    <div class="d-flex justify-content-between mt-4">

                        <a href="{{ route('asset-categories.index') }}"
                           class="btn btn-secondary">

                            <i class="bi bi-arrow-left me-1"></i>
                            Back

                        </a>

                        <button type="submit"
                                class="btn btn-primary">

                            <i class="bi bi-check-circle me-1"></i>
                            Create Category

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

</div>

@endsection
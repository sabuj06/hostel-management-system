@extends('layouts.app')

@section('title', 'Add Role')
@section('page-title', 'Add Role')

@section('content')
<div class="card shadow-sm border-0" style="max-width:600px;">
    <div class="card-body">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('roles.store') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">Internal Name (lowercase, no spaces)</label>
                <input type="text" name="name" class="form-control" placeholder="e.g. accountant" value="{{ old('name') }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Display Label</label>
                <input type="text" name="label" class="form-control" placeholder="e.g. Accountant" value="{{ old('label') }}" required>
            </div>
            <button class="btn btn-primary">Create Role</button>
            <a href="{{ route('roles.index') }}" class="btn btn-light">Cancel</a>
        </form>
    </div>
</div>
@endsection
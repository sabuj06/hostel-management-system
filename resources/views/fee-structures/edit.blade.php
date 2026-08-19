@extends('layouts.app')

@section('title', 'Edit Fee Structure')
@section('page-title', 'Edit Fee Structure')

@section('content')
<div class="card shadow-sm border-0" style="max-width:700px;">
    <div class="card-body">
        <form method="POST" action="{{ route('fee-structures.update', $feeStructure) }}">
            @csrf
            @method('PUT')
            @include('fee-structures._form', ['feeStructure' => $feeStructure])
            <button class="btn btn-primary">Update Fee Structure</button>
            <a href="{{ route('fee-structures.index') }}" class="btn btn-light">Cancel</a>
        </form>
    </div>
</div>
@endsection
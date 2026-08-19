@extends('layouts.app')

@section('title', 'Add Fee Structure')
@section('page-title', 'Add Fee Structure')

@section('content')
<div class="card shadow-sm border-0" style="max-width:700px;">
    <div class="card-body">
        <form method="POST" action="{{ route('fee-structures.store') }}">
            @csrf
            @include('fee-structures._form')
            <button class="btn btn-primary">Save Fee Structure</button>
            <a href="{{ route('fee-structures.index') }}" class="btn btn-light">Cancel</a>
        </form>
    </div>
</div>
@endsection
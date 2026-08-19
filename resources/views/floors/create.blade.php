@extends('layouts.app')

@section('title', 'Add Floor')
@section('page-title', 'Add Floor')

@section('content')
<div class="card shadow-sm border-0" style="max-width:700px;">
    <div class="card-body">
        <form method="POST" action="{{ route('floors.store') }}">
            @csrf
            @include('floors._form')
            <button class="btn btn-primary">Save Floor</button>
            <a href="{{ route('floors.index') }}" class="btn btn-light">Cancel</a>
        </form>
    </div>
</div>
@endsection
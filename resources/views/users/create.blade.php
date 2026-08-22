@extends('layouts.app')

@section('title', 'Add User')
@section('page-title', 'Add User')

@section('content')
<div class="card shadow-sm border-0" style="max-width:700px;">
    <div class="card-body">
        <form method="POST" action="{{ route('users.store') }}">
            @csrf
            @include('users._form')
            <button class="btn btn-primary">Create User</button>
            <a href="{{ route('users.index') }}" class="btn btn-light">Cancel</a>
        </form>
    </div>
</div>
@endsection
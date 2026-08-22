@extends('layouts.app')

@section('title', 'Edit User')
@section('page-title', 'Edit User')

@section('content')
<div class="card shadow-sm border-0" style="max-width:700px;">
    <div class="card-body">
        <form method="POST" action="{{ route('users.update', $user) }}">
            @csrf
            @method('PUT')
            @include('users._form', ['user' => $user])
            <button class="btn btn-primary">Update User</button>
            <a href="{{ route('users.index') }}" class="btn btn-light">Cancel</a>
        </form>
    </div>
</div>
@endsection
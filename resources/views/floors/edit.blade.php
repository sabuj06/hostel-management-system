@extends('layouts.app')

@section('title', 'Edit Floor')
@section('page-title', 'Edit Floor')

@section('content')
<div class="card shadow-sm border-0" style="max-width:700px;">
    <div class="card-body">
        <form method="POST" action="{{ route('floors.update', $floor) }}">
            @csrf
            @method('PUT')
            @include('floors._form', ['floor' => $floor])
            <button class="btn btn-primary">Update Floor</button>
            <a href="{{ route('floors.index') }}" class="btn btn-light">Cancel</a>
        </form>
    </div>
</div>
@endsection
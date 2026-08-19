@extends('layouts.app')

@section('title', 'Add Room')
@section('page-title', 'Add Room')

@section('content')
<div class="card shadow-sm border-0" style="max-width:700px;">
    <div class="card-body">
        <form method="POST" action="{{ route('rooms.store') }}">
            @csrf
            @include('rooms._form')
            <button class="btn btn-primary">Save Room</button>
            <a href="{{ route('rooms.index') }}" class="btn btn-light">Cancel</a>
        </form>
    </div>
</div>
@endsection
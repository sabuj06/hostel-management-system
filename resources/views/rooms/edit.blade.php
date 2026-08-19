@extends('layouts.app')

@section('title', 'Edit Room')
@section('page-title', 'Edit Room')

@section('content')
<div class="card shadow-sm border-0" style="max-width:700px;">
    <div class="card-body">
        <form method="POST" action="{{ route('rooms.update', $room) }}">
            @csrf
            @method('PUT')
            @include('rooms._form', ['room' => $room])
            <button class="btn btn-primary">Update Room</button>
            <a href="{{ route('rooms.index') }}" class="btn btn-light">Cancel</a>
        </form>
    </div>
</div>
@endsection
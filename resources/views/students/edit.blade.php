@extends('layouts.app')

@section('title', 'Edit Student')
@section('page-title', 'Edit Student')

@section('content')
<div class="card shadow-sm border-0" style="max-width:800px;">
    <div class="card-body">
        <form method="POST" action="{{ route('students.update', $student) }}">
            @csrf
            @method('PUT')
            @include('students._form', ['student' => $student])
            <button class="btn btn-primary">Update Student</button>
            <a href="{{ route('students.show', $student) }}" class="btn btn-light">Cancel</a>
        </form>
    </div>
</div>
@endsection
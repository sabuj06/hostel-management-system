@extends('layouts.app')

@section('title', 'Add Student')
@section('page-title', 'Add Student')

@section('content')
<div class="card shadow-sm border-0" style="max-width:800px;">
    <div class="card-body">
        <form method="POST" action="{{ route('students.store') }}">
            @csrf
            @include('students._form')
            <button class="btn btn-primary">Save Student</button>
            <a href="{{ route('students.index') }}" class="btn btn-light">Cancel</a>
        </form>
    </div>
</div>
@endsection
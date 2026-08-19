@extends('layouts.app')

@section('title', 'Add Hostel')
@section('page-title', 'Add Hostel')

@section('content')
<div class="card shadow-sm border-0" style="max-width:700px;">
    <div class="card-body">
        <form method="POST" action="{{ route('hostels.store') }}">
            @csrf
            @include('hostels._form')
            <button class="btn btn-primary">Save Hostel</button>
            <a href="{{ route('hostels.index') }}" class="btn btn-light">Cancel</a>
        </form>
    </div>
</div>
@endsection
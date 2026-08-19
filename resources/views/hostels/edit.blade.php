@extends('layouts.app')

@section('title', 'Edit Hostel')
@section('page-title', 'Edit Hostel')

@section('content')
<div class="card shadow-sm border-0" style="max-width:700px;">
    <div class="card-body">
        <form method="POST" action="{{ route('hostels.update', $hostel) }}">
            @csrf
            @method('PUT')
            @include('hostels._form', ['hostel' => $hostel])
            <button class="btn btn-primary">Update Hostel</button>
            <a href="{{ route('hostels.index') }}" class="btn btn-light">Cancel</a>
        </form>
    </div>
</div>
@endsection
@extends('layouts.app')

@section('title', 'Add Block')
@section('page-title', 'Add Block')

@section('content')
<div class="card shadow-sm border-0" style="max-width:700px;">
    <div class="card-body">
        <form method="POST" action="{{ route('blocks.store') }}">
            @csrf
            @include('blocks._form')
            <button class="btn btn-primary">Save Block</button>
            <a href="{{ route('blocks.index') }}" class="btn btn-light">Cancel</a>
        </form>
    </div>
</div>
@endsection
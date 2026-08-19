@extends('layouts.app')

@section('title', 'Edit Block')
@section('page-title', 'Edit Block')

@section('content')
<div class="card shadow-sm border-0" style="max-width:700px;">
    <div class="card-body">
        <form method="POST" action="{{ route('blocks.update', $block) }}">
            @csrf
            @method('PUT')
            @include('blocks._form', ['block' => $block])
            <button class="btn btn-primary">Update Block</button>
            <a href="{{ route('blocks.index') }}" class="btn btn-light">Cancel</a>
        </form>
    </div>
</div>
@endsection
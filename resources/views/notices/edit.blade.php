@extends('layouts.app')

@section('title', 'Edit Notice')
@section('page-title', 'Edit Notice')

@section('content')
<div class="card shadow-sm border-0" style="max-width:750px;">
    <div class="card-body">
        <form method="POST" action="{{ route('notices.update', $notice) }}">
            @csrf
            @method('PUT')
            @include('notices._form', ['notice' => $notice])
            <button class="btn btn-primary">Update Notice</button>
            <a href="{{ route('notices.manage') }}" class="btn btn-light">Cancel</a>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(function () {
        $('#audience').on('change', function () {
            $('#hostel-field').toggle($(this).val() === 'hostel');
        });
    });
</script>
@endpush
@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="row g-3">
    <div class="col-md-4">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="text-muted small">Total Users</div>
                <div class="fs-3 fw-bold">{{ $stats['total_users'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="text-muted small">Active Users</div>
                <div class="fs-3 fw-bold text-success">{{ $stats['active_users'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="text-muted small">Your Role</div>
                <div class="fs-5 fw-bold text-primary">{{ $stats['role'] }}</div>
            </div>
        </div>
    </div>
</div>

<div class="alert alert-info mt-4">
    Phase 1 (Authentication + Roles) is now live. Room/Bed Management, Fees, Complaints, and other features will be added in the upcoming phases.
</div>
@endsection
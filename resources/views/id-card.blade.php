@extends('layouts.app')

@section('title', 'Student ID Card')
@section('page-title', 'Student ID Card')

@section('content')
<div class="d-flex justify-content-center">
    <div class="card shadow border-0" id="id-card" style="width:350px;">
        <div class="card-body text-center p-4">
            <div class="fw-bold text-primary mb-1">HOSTEL MANAGEMENT SYSTEM</div>
            <div class="small text-muted mb-3">Student Identity Card</div>

            <div class="fw-bold fs-5">{{ $student->name }}</div>
            <div class="text-muted small mb-3">{{ $student->student_uid }}</div>

            <!-- QR code — encodes the student's unique token, scanned at the gate for attendance -->
            <img
                src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={{ urlencode($student->qr_token) }}"
                alt="QR Code"
                class="img-fluid border rounded p-2 mb-3"
                style="width:200px; height:200px;"
            >

            <table class="table table-sm text-start small mb-0">
                <tr><td class="text-muted">Room</td><td class="text-end">{{ $student->currentAllocation->room->room_number ?? 'Not allocated' }}</td></tr>
                <tr><td class="text-muted">Course</td><td class="text-end">{{ $student->course ?? '-' }}</td></tr>
                <tr><td class="text-muted">Valid Until</td><td class="text-end">{{ now()->addYear()->format('d M Y') }}</td></tr>
            </table>
        </div>
    </div>
</div>

<div class="text-center mt-3 no-print">
    <button class="btn btn-primary" onclick="window.print()"><i class="bi bi-printer"></i> Print ID Card</button>
    <a href="{{ route('students.show', $student) }}" class="btn btn-light">Back to Profile</a>
</div>

<style>
    @media print {
        .no-print, .sidebar, .navbar { display: none !important; }
        #id-card { box-shadow: none !important; border: 2px solid #000 !important; }
    }
</style>
@endsection
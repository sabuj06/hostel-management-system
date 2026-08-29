@extends('layouts.student')

@section('title', 'My QR Code')
@section('page-title', 'My Attendance QR Code')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow-sm border-0 text-center">
            <div class="card-body p-4">

                <h5 class="mb-1">{{ $student->name }}</h5>
                <p class="text-muted small mb-3">{{ $student->student_uid }}</p>

                <div id="qr-code-display" class="d-flex justify-content-center mb-3"></div>

                <p class="text-muted small mb-0">
                    Show this QR code to hostel staff at the gate or attendance desk to mark your attendance.
                </p>

            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    new QRCode(document.getElementById('qr-code-display'), {
        text: @json($student->qr_token),
        width: 220,
        height: 220,
    });
});
</script>
@endpush
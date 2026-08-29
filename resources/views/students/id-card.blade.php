@extends('layouts.app')

@section('title', 'Student ID Card')
@section('page-title', 'Student ID Card')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4 text-center">

                <h5 class="mb-1">{{ $student->name }}</h5>
                <p class="text-muted small mb-1">{{ $student->student_uid }}</p>

                @if($student->currentAllocation?->room)
                    <p class="text-muted small mb-3">
                        Room {{ $student->currentAllocation->room->room_number }}
                    </p>
                @endif

                <div id="qr-code-display" class="d-flex justify-content-center my-3"></div>

                <p class="text-muted small mb-3">
                    Scan this code at the gate or attendance desk to mark {{ $student->name }}'s attendance.
                </p>

                <button onclick="window.print()" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-printer me-1"></i> Print ID Card
                </button>

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
        width: 200,
        height: 200,
    });
});
</script>
@endpush
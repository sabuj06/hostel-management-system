@extends('layouts.app')

@section('title', 'QR Scanner')
@section('page-title', 'QR Attendance Scanner')

@section('content')

<div class="card shadow-sm">
    <div class="card-body">

        <h5 class="mb-3">
            <i class="bi bi-qr-code-scan me-2"></i>
            Scan Student QR Code
        </h5>

        <div id="qr-reader" style="width: 100%; max-width: 500px;"></div>

        <div id="qr-result" class="mt-3"></div>

    </div>
</div>

@endsection

@push('scripts')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const resultBox = document.getElementById('qr-result');

    function onScanSuccess(decodedText) {

        // Stop scanning immediately after a successful read
        html5QrCode.stop();

        resultBox.innerHTML =
            '<div class="alert alert-info">Scanned: ' + decodedText + ' — processing...</div>';

        fetch('{{ route("attendance.qr-scan") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ token: decodedText })
        })
        .then(response => response.json())
        .then(data => {
            const alertClass = data.success ? 'alert-success' : 'alert-danger';
            let details = '';

            if (data.success) {
                details = `<br><small>${data.student_name} (${data.student_uid}) — Room ${data.room ?? 'N/A'}${data.is_late ? ' — <strong>LATE</strong>' : ''}</small>`;
            }

            resultBox.innerHTML =
                `<div class="alert ${alertClass}">${data.message}${details}</div>`;

            // Restart scanning after a short pause
            setTimeout(() => {
                html5QrCode.start(
                    { facingMode: "environment" },
                    { fps: 10, qrbox: 250 },
                    onScanSuccess
                );
            }, 2000);
        })
        .catch(() => {
            resultBox.innerHTML =
                '<div class="alert alert-danger">Something went wrong. Please try again.</div>';
        });
    }

    const html5QrCode = new Html5Qrcode("qr-reader");

    html5QrCode.start(
        { facingMode: "environment" },
        { fps: 10, qrbox: 250 },
        onScanSuccess
    ).catch(err => {
        resultBox.innerHTML =
            '<div class="alert alert-warning">Could not start camera: ' + err + '</div>';
    });

});
</script>
@endpush
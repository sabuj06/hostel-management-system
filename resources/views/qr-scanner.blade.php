@extends('layouts.app')

@section('title', 'QR Attendance Scanner')
@section('page-title', 'Gate — QR Attendance Scanner')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div id="qr-reader" style="width:100%;"></div>
                <div id="qr-reader-status" class="text-center text-muted small mt-2">Point the camera at a student's ID card QR code.</div>
            </div>
        </div>

        <!-- Result banner -->
        <div id="result-banner" class="alert mt-3 d-none" role="alert"></div>

        <!-- Recent scans log -->
        <div class="card shadow-sm border-0 mt-3">
            <div class="card-body">
                <h6 class="mb-3">Recent Scans (this session)</h6>
                <div id="recent-scans" class="list-group">
                    <div class="text-muted small">No scans yet.</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
    $(function () {
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

        let isProcessing = false;
        let recentScansCount = 0;

        const scanner = new Html5Qrcode('qr-reader');

        function showResult(success, message) {
            const $banner = $('#result-banner');
            $banner
                .removeClass('d-none alert-success alert-danger')
                .addClass(success ? 'alert-success' : 'alert-danger')
                .text(message);

            // Auto-hide after a few seconds so the gate staff can keep scanning
            setTimeout(() => $banner.addClass('d-none'), 4000);
        }

        function logScan(success, text) {
            if (recentScansCount === 0) {
                $('#recent-scans').empty();
            }
            recentScansCount++;

            const icon = success ? '<i class="bi bi-check-circle text-success"></i>' : '<i class="bi bi-x-circle text-danger"></i>';
            $('#recent-scans').prepend(`
                <div class="list-group-item small d-flex justify-content-between align-items-center">
                    <span>${icon} ${text}</span>
                    <span class="text-muted">${new Date().toLocaleTimeString()}</span>
                </div>
            `);
        }

        function onScanSuccess(decodedText) {
            if (isProcessing) return; // ignore rapid repeat reads of the same frame
            isProcessing = true;

            $('#qr-reader-status').text('Verifying...');

            $.ajax({
                url: '{{ route('attendance.qr-scan') }}',
                method: 'POST',
                data: { token: decodedText },
                success: function (res) {
                    showResult(true, `${res.student_name} (${res.student_uid}) — ${res.message}${res.is_late ? ' (Late)' : ''}`);
                    logScan(true, `${res.student_name} — ${res.room ? 'Room ' + res.room : 'No room'}${res.is_late ? ' — Late' : ''}`);
                },
                error: function (xhr) {
                    const msg = xhr.responseJSON?.message || 'Scan failed. Please try again.';
                    showResult(false, msg);
                    logScan(false, msg);
                },
                complete: function () {
                    $('#qr-reader-status').text("Point the camera at a student's ID card QR code.");
                    // Small delay before allowing the next scan, avoids double-fires
                    setTimeout(() => { isProcessing = false; }, 2000);
                }
            });
        }

        scanner.start(
            { facingMode: 'environment' },
            { fps: 10, qrbox: { width: 250, height: 250 } },
            onScanSuccess
        ).catch(function (err) {
            $('#qr-reader-status').html('<span class="text-danger">Could not access camera: ' + err + '</span>');
        });
    });
</script>
@endpush
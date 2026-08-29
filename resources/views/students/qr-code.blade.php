@extends('layouts.app')

@section('title', 'Attendance QR Code')
@section('page-title', 'Attendance QR Code')

@section('content')

<div class="container-fluid">

    <div class="row justify-content-center">

        <div class="col-md-5">

            <div class="card shadow-sm border-0">

                <div class="card-body text-center p-4">

                    <h4 class="fw-bold mb-1">
                        {{ $student->name }}
                    </h4>

                    <div class="text-muted mb-3">
                        Student ID:
                        <strong>{{ $student->student_uid }}</strong>
                    </div>

                    {{-- QR CODE --}}
                    <div class="border rounded p-4 bg-white">

                        {!! QrCode::size(250)->generate($qrToken->token) !!}

                    </div>

                    {{-- Token --}}
                    <div class="mt-3">

                        <div class="small text-muted">
                            Attendance QR Token
                        </div>

                        <code class="small">
                            {{ $qrToken->token }}
                        </code>

                    </div>

                    {{-- Countdown --}}
                    <div class="mt-4">

                        <div class="text-muted small">
                            QR expires in
                        </div>

                        <div
                            id="countdown"
                            class="fs-3 fw-bold text-primary"
                        >
                            10:00
                        </div>

                    </div>

                    {{-- Expired message --}}
                    <div
                        id="expiredMessage"
                        class="alert alert-danger mt-3 d-none"
                    >
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        This QR code has expired.
                    </div>

                    {{-- Buttons --}}
                    <div class="mt-4">

                        <button
                            onclick="window.print()"
                            class="btn btn-primary"
                        >
                            <i class="bi bi-printer me-1"></i>
                            Print QR Code
                        </button>

                        <a
                            href="{{ route('students.index') }}"
                            class="btn btn-secondary"
                        >
                            Back
                        </a>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection


@push('scripts')

<script>

    const expiresAt = new Date(
        "{{ $qrToken->expires_at->toIso8601String() }}"
    ).getTime();

    const countdownElement =
        document.getElementById('countdown');

    const expiredMessage =
        document.getElementById('expiredMessage');


    const timer = setInterval(function () {

        const now = new Date().getTime();

        const distance = expiresAt - now;


        if (distance <= 0) {

            clearInterval(timer);

            countdownElement.innerText = 'Expired';

            countdownElement.classList.remove('text-primary');
            countdownElement.classList.add('text-danger');

            expiredMessage.classList.remove('d-none');

            return;
        }


        const minutes =
            Math.floor(
                (distance % (1000 * 60 * 60)) /
                (1000 * 60)
            );

        const seconds =
            Math.floor(
                (distance % (1000 * 60)) /
                1000
            );


        countdownElement.innerText =
            String(minutes).padStart(2, '0') +
            ':' +
            String(seconds).padStart(2, '0');

    }, 1000);

</script>

@endpush
@extends('layouts.student')

@section('title', 'My Mess Cuts')
@section('page-title', 'My Mess Cuts')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-1">
            <i class="bi bi-cup-hot me-2"></i>
            My Mess Cuts
        </h4>
        <p class="text-muted mb-0">
            View your mess cut requests and their status.
        </p>
    </div>

    <a href="{{ route('student-portal.mess-cuts.create') }}"
       class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i>
        Request Mess Cut
    </a>
</div>


@if(session('status'))
    <div class="alert alert-success">
        <i class="bi bi-check-circle me-2"></i>
        {{ session('status') }}
    </div>
@endif


<div class="card shadow-sm border-0">

    <div class="card-body">

        @if($messCuts->count())

            <div class="table-responsive">

                <table class="table table-hover align-middle">

                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>From Date</th>
                            <th>To Date</th>
                            <th>Days</th>
                            <th>Reason</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>

                        @foreach($messCuts as $messCut)

                            <tr>

                                <td>
                                    {{ $loop->iteration + (($messCuts->currentPage() - 1) * $messCuts->perPage()) }}
                                </td>

                                <td>
                                    {{ $messCut->from_date
                                        ? \Carbon\Carbon::parse($messCut->from_date)->format('d M Y')
                                        : '-' }}
                                </td>

                                <td>
                                    {{ $messCut->to_date
                                        ? \Carbon\Carbon::parse($messCut->to_date)->format('d M Y')
                                        : '-' }}
                                </td>

                                <td>
                                    @if($messCut->from_date && $messCut->to_date)
                                        {{ \Carbon\Carbon::parse($messCut->from_date)
                                            ->diffInDays(
                                                \Carbon\Carbon::parse($messCut->to_date)
                                            ) + 1 }}
                                    @else
                                        -
                                    @endif
                                </td>

                                <td>
                                    {{ $messCut->reason ?? '-' }}
                                </td>

                                <td>

                                    @php
                                        $status = strtolower($messCut->status ?? 'pending');

                                        $badge = match($status) {
                                            'approved' => 'success',
                                            'rejected' => 'danger',
                                            'cancelled' => 'secondary',
                                            default => 'warning',
                                        };
                                    @endphp

                                    <span class="badge bg-{{ $badge }}">
                                        {{ ucfirst($status) }}
                                    </span>

                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

            <div class="mt-3">
                {{ $messCuts->links() }}
            </div>

        @else

            <div class="text-center py-5">

                <i class="bi bi-cup-hot display-4 text-muted"></i>

                <h5 class="mt-3">
                    No Mess Cut Requests
                </h5>

                <p class="text-muted">
                    You have not submitted any mess cut request yet.
                </p>

                <a href="{{ route('student-portal.mess-cuts.create') }}"
                   class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i>
                    Request Mess Cut
                </a>

            </div>

        @endif

    </div>

</div>

@endsection
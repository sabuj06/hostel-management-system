@extends('layouts.student')

@section('title', 'My Mess Cuts')
@section('page-title', 'My Mess Cuts')

@section('content')
<div class="d-flex justify-content-end mb-3">
    <a href="{{ route('student-portal.mess-cuts.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Request Mess Cut</a>
</div>

<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr><th>From</th><th>To</th><th>Days</th><th>Reason</th></tr>
            </thead>
            <tbody>
                @forelse($messCuts as $cut)
                <tr>
                    <td>{{ $cut->from_date->format('d M Y') }}</td>
                    <td>{{ $cut->to_date->format('d M Y') }}</td>
                    <td><span class="badge bg-light text-dark border">{{ $cut->dayCount() }} day(s)</span></td>
                    <td>{{ $cut->reason ?? '-' }}</td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center text-muted py-4">You haven't requested any mess cuts yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">{{ $messCuts->links() }}</div>
</div>
@endsection
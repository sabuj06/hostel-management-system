@extends('layouts.app')

@section('title', 'Mess Cuts')
@section('page-title', 'Mess Cut Records')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <form method="GET" class="d-flex gap-2">
        <input type="text" name="search" class="form-control" placeholder="Search student..." value="{{ request('search') }}">
        <button class="btn btn-outline-secondary"><i class="bi bi-search"></i></button>
    </form>
    <div class="d-flex gap-2">
        <a href="{{ route('mess-cuts.rates') }}" class="btn btn-outline-secondary"><i class="bi bi-currency-exchange"></i> Mess Rates</a>
        <a href="{{ route('mess-bills.create') }}" class="btn btn-outline-secondary"><i class="bi bi-receipt"></i> Generate Bills</a>
        <a href="{{ route('mess-cuts.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add Mess Cut</a>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr><th>Student</th><th>From</th><th>To</th><th>Days</th><th>Reason</th><th></th></tr>
            </thead>
            <tbody>
                @forelse($messCuts as $cut)
                <tr>
                    <td>
                        <a href="{{ route('students.show', $cut->student) }}">{{ $cut->student->name }}</a>
                        <div class="small text-muted">{{ $cut->student->student_uid }}</div>
                    </td>
                    <td>{{ $cut->from_date->format('d M Y') }}</td>
                    <td>{{ $cut->to_date->format('d M Y') }}</td>
                    <td><span class="badge bg-light text-dark border">{{ $cut->dayCount() }} day(s)</span></td>
                    <td>{{ $cut->reason ?? '-' }}</td>
                    <td class="text-end">
                        <form action="{{ route('mess-cuts.destroy', $cut) }}" method="POST" onsubmit="return confirm('Remove this mess cut?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No mess cuts recorded.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">{{ $messCuts->links() }}</div>
</div>
@endsection
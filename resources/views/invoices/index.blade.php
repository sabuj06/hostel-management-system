@extends('layouts.app')

@section('title', 'Invoices')
@section('page-title', 'Invoices')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <form method="GET" class="d-flex gap-2">
        <input type="text" name="search" class="form-control" placeholder="Search student..." value="{{ request('search') }}">
        <select name="status" class="form-select" onchange="this.form.submit()">
            <option value="">All Status</option>
            <option value="unpaid" @selected(request('status') === 'unpaid')>Unpaid</option>
            <option value="partial" @selected(request('status') === 'partial')>Partial</option>
            <option value="paid" @selected(request('status') === 'paid')>Paid</option>
            <option value="overdue" @selected(request('status') === 'overdue')>Overdue</option>
        </select>
        <button class="btn btn-outline-secondary"><i class="bi bi-search"></i></button>
    </form>
    <a href="{{ route('invoices.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Generate Invoice</a>
</div>

<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Invoice No.</th>
                    <th>Student</th>
                    <th>Period</th>
                    <th>Amount</th>
                    <th>Paid</th>
                    <th>Balance</th>
                    <th>Due Date</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoices as $inv)
                <tr>
                    <td>{{ $inv->invoice_no }}</td>
                    <td>
                        <a href="{{ route('students.show', $inv->student) }}">{{ $inv->student->name }}</a>
                    </td>
                    <td>{{ $inv->period ?? '-' }}</td>
                    <td>₹{{ number_format($inv->amount, 2) }}</td>
                    <td>₹{{ number_format($inv->paid_amount, 2) }}</td>
                    <td class="fw-semibold">₹{{ number_format($inv->balance(), 2) }}</td>
                    <td>{{ $inv->due_date->format('d M Y') }}</td>
                    <td>
                        @php
                            $badge = ['unpaid' => 'secondary', 'partial' => 'warning', 'paid' => 'success', 'overdue' => 'danger', 'cancelled' => 'dark'][$inv->status];
                        @endphp
                        <span class="badge bg-{{ $badge }} text-capitalize">{{ $inv->status }}</span>
                    </td>
                    <td class="text-end">
                        <a href="{{ route('invoices.show', $inv) }}" class="btn btn-sm btn-outline-primary">View / Pay</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="text-center text-muted py-4">No invoices found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">{{ $invoices->links() }}</div>
</div>
@endsection
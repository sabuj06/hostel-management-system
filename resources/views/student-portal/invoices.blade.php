@extends('layouts.student')

@section('title', 'My Invoices')
@section('page-title', 'My Invoices')

@section('content')
<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Invoice No.</th>
                    <th>Period</th>
                    <th>Amount</th>
                    <th>Paid</th>
                    <th>Balance</th>
                    <th>Due Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoices as $inv)
                <tr>
                    <td>{{ $inv->invoice_no }}</td>
                    <td>{{ $inv->period ?? '-' }}</td>
                    <td>৳{{ number_format($inv->amount, 2) }}</td>
                    <td>৳{{ number_format($inv->paid_amount, 2) }}</td>
                    <td class="fw-semibold">৳{{ number_format($inv->balance(), 2) }}</td>
                    <td>{{ $inv->due_date->format('d M Y') }}</td>
                    <td>
                        @php
                            $badge = ['unpaid' => 'secondary', 'partial' => 'warning', 'paid' => 'success', 'overdue' => 'danger', 'cancelled' => 'dark'][$inv->status];
                        @endphp
                        <span class="badge bg-{{ $badge }} text-capitalize">{{ $inv->status }}</span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-4">No invoices yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">{{ $invoices->links() }}</div>
</div>

<div class="alert alert-info mt-3 small">
    <i class="bi bi-info-circle"></i> To make a payment, please visit the hostel office. Online payment will be added in a future update.
</div>
@endsection
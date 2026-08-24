@extends('layouts.app')

@section('title', 'Invoice ' . $invoice->invoice_no)
@section('page-title', 'Invoice Details')

@section('content')
<div class="row g-3">
    <div class="col-md-5">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h6 class="mb-1">{{ $invoice->invoice_no }}</h6>
                        <div class="text-muted small">{{ $invoice->period ?? '-' }}</div>
                    </div>
                    <span id="status-badge" class="badge bg-{{ ['unpaid' => 'secondary', 'partial' => 'warning', 'paid' => 'success', 'overdue' => 'danger', 'cancelled' => 'dark'][$invoice->status] }} text-capitalize">
                        {{ $invoice->status }}
                    </span>
                </div>

                <ul class="list-unstyled small mb-3">
                    <li class="mb-2"><strong>Student:</strong> <a href="{{ route('students.show', $invoice->student) }}">{{ $invoice->student->name }}</a> ({{ $invoice->student->student_uid }})</li>
                    <li class="mb-2"><strong>Fee:</strong> {{ $invoice->feeStructure->name ?? '-' }}</li>
                    <li class="mb-2"><strong>Due Date:</strong> {{ $invoice->due_date->format('d M Y') }}</li>
                </ul>

                <table class="table table-sm mb-0">
                    <tr><td>Total Amount</td><td class="text-end">₹{{ number_format($invoice->amount, 2) }}</td></tr>
                    <tr><td>Paid Amount</td><td class="text-end" id="paid-amount">₹{{ number_format($invoice->paid_amount, 2) }}</td></tr>
                    <tr class="fw-bold"><td>Balance</td><td class="text-end" id="balance-amount">₹{{ number_format($invoice->balance(), 2) }}</td></tr>
                </table>
            </div>
        </div>

        @if($invoice->balance() > 0)
        <div class="card shadow-sm border-0 mt-3">
            <div class="card-body">
                <h6 class="mb-3">Record Payment</h6>
                <form id="paymentForm">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label">Amount</label>
                        <input type="number" step="0.01" name="amount" id="pay_amount" class="form-control" max="{{ $invoice->balance() }}" value="{{ $invoice->balance() }}" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Payment Date</label>
                        <input type="date" name="payment_date" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Method</label>
                        <select name="method" class="form-select">
                            <option value="cash">Cash</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="mobile_banking">Mobile Banking</option>
                            <option value="card">Card</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Transaction Ref (optional)</label>
                        <input type="text" name="transaction_ref" class="form-control">
                    </div>
                    <div id="payment-error" class="text-danger small mb-2" style="display:none;"></div>
                    <button type="submit" class="btn btn-success w-100">Record Payment</button>
                </form>
            </div>
        </div>
        @endif
    </div>

    <div class="col-md-7">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h6 class="mb-3">Payment History</h6>
                <div class="table-responsive">
                    <table class="table table-sm mb-0" id="payments-table">
                        <thead class="table-light">
                            <tr><th>Payment No.</th><th>Amount</th><th>Date</th><th>Method</th><th>Received By</th><th></th></tr>
                        </thead>
                        <tbody id="payments-tbody">
                            @forelse($invoice->payments as $payment)
                            <tr data-payment-id="{{ $payment->id }}">
                                <td>{{ $payment->payment_no }}</td>
                                <td>₹{{ number_format($payment->amount, 2) }}</td>
                                <td>{{ $payment->payment_date->format('d M Y') }}</td>
                                <td class="text-capitalize">{{ str_replace('_', ' ', $payment->method) }}</td>
                                <td>{{ $payment->receivedBy->name ?? '-' }}</td>
                                <td>
                                    <button class="btn btn-sm btn-outline-danger delete-payment" data-payment-id="{{ $payment->id }}"><i class="bi bi-trash"></i></button>
                                </td>
                            </tr>
                            @empty
                            <tr id="no-payments-row"><td colspan="6" class="text-center text-muted py-3">No payments recorded yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(function () {
        $.ajaxSetup({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
        });

        const statusBadgeClass = {
            unpaid: 'bg-secondary', partial: 'bg-warning', paid: 'bg-success',
            overdue: 'bg-danger', cancelled: 'bg-dark'
        };

        // Record payment via AJAX — updates balance/status/history without reload
        $('#paymentForm').on('submit', function (e) {
            e.preventDefault();
            const $form = $(this);
            const $btn = $form.find('button[type=submit]').prop('disabled', true).text('Saving...');
            $('#payment-error').hide();

            $.ajax({
                url: '{{ route('payments.store', $invoice) }}',
                method: 'POST',
                data: $form.serialize(),
                success: function (res) {
                    // Update summary card
                    $('#paid-amount').text('₹' + parseFloat(res.invoice.paid_amount).toFixed(2));
                    $('#balance-amount').text('₹' + parseFloat(res.invoice.balance).toFixed(2));
                    $('#status-badge')
                        .attr('class', 'badge text-capitalize ' + statusBadgeClass[res.invoice.status])
                        .text(res.invoice.status);

                    // Append to payment history
                    $('#no-payments-row').remove();
                    const p = res.payment;
                    $('#payments-tbody').append(`
                        <tr data-payment-id="${p.id}">
                            <td>${p.payment_no}</td>
                            <td>₹${parseFloat(p.amount).toFixed(2)}</td>
                            <td>${p.payment_date}</td>
                            <td class="text-capitalize">${p.method.replace('_',' ')}</td>
                            <td>${p.received_by ? p.received_by.name : '-'}</td>
                            <td><button class="btn btn-sm btn-outline-danger delete-payment" data-payment-id="${p.id}"><i class="bi bi-trash"></i></button></td>
                        </tr>
                    `);

                    if (res.invoice.balance <= 0) {
                        $('#paymentForm').closest('.card').fadeOut();
                    } else {
                        $('#pay_amount').attr('max', res.invoice.balance).val(res.invoice.balance);
                    }

                    $form[0].reset();
                },
                error: function (xhr) {
                    const msg = xhr.responseJSON?.message || 'Failed to record payment.';
                    $('#payment-error').text(msg).show();
                },
                complete: function () {
                    $btn.prop('disabled', false).text('Record Payment');
                }
            });
        });

        // Delete payment via AJAX
        $(document).on('click', '.delete-payment', function () {
            if (!confirm('Remove this payment record?')) return;
            const $row = $(this).closest('tr');
            const id = $(this).data('payment-id');

            $.ajax({
                url: `/payments/${id}`,
                method: 'DELETE',
                success: function (res) {
                    $row.fadeOut(200, () => {
                        $row.remove();
                        $('#paid-amount').text('₹' + parseFloat(res.invoice.paid_amount).toFixed(2));
                        $('#balance-amount').text('₹' + parseFloat(res.invoice.balance).toFixed(2));
                        $('#status-badge')
                            .attr('class', 'badge text-capitalize ' + statusBadgeClass[res.invoice.status])
                            .text(res.invoice.status);
                    });
                },
                error: function () {
                    alert('Failed to remove payment.');
                }
            });
        });
    });
</script>
@endpush
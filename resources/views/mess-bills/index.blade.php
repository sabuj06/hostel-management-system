@extends('layouts.app')

@section('title', 'Generated Mess Bills')
@section('page-title', 'Generated Mess Bills')

@section('content')

<div class="card shadow-sm border-0">

    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0">
                <i class="bi bi-receipt"></i>
                Generated Mess Bills
            </h5>
            <small class="text-muted">
                All generated mess fee invoices
            </small>
        </div>

        <a href="{{ route('mess-bills.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i>
            Generate New Bills
        </a>
    </div>

    <div class="card-body">

        @if(session('status'))
            <div class="alert alert-success">
                <i class="bi bi-check-circle"></i>
                {{ session('status') }}
            </div>
        @endif

        @if($invoices->count())

            <div class="table-responsive">

                <table class="table table-hover align-middle">

                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Invoice No.</th>
                            <th>Student</th>
                            <th>Period</th>
                            <th>Amount</th>
                            <th>Due Date</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>

                    <tbody>

                        @foreach($invoices as $invoice)

                            <tr>

                                <td>
                                    {{ $invoices->firstItem() + $loop->index }}
                                </td>

                                <td>
                                    <strong>
                                        {{ $invoice->invoice_no }}
                                    </strong>
                                </td>

                                <td>
                                    @if($invoice->student)
                                        <div class="fw-semibold">
                                            {{ $invoice->student->name }}
                                        </div>

                                        @if(isset($invoice->student->registration_no))
                                            <small class="text-muted">
                                                {{ $invoice->student->registration_no }}
                                            </small>
                                        @endif
                                    @else
                                        <span class="text-muted">
                                            Student not found
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    {{ $invoice->period }}
                                </td>

                                <td>
                                    <strong>
                                        ₹{{ number_format($invoice->amount, 2) }}
                                    </strong>
                                </td>

                                <td>
                                    {{ $invoice->due_date?->format('d M Y') ?? '-' }}
                                </td>

                                <td>

                                    @if($invoice->status === 'paid')

                                        <span class="badge bg-success">
                                            Paid
                                        </span>

                                    @elseif($invoice->status === 'partial')

                                        <span class="badge bg-warning text-dark">
                                            Partial
                                        </span>

                                    @elseif($invoice->status === 'overdue')

                                        <span class="badge bg-danger">
                                            Overdue
                                        </span>

                                    @else

                                        <span class="badge bg-secondary">
                                            Unpaid
                                        </span>

                                    @endif

                                </td>

                                <td class="text-end">

                                    <a href="{{ route('mess-bills.show', $invoice) }}"
                                       class="btn btn-sm btn-outline-primary">

                                        <i class="bi bi-eye"></i>
                                        View

                                    </a>

                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

            <div class="mt-3">
                {{ $invoices->links() }}
            </div>

        @else

            <div class="text-center py-5">

                <i class="bi bi-receipt fs-1 text-muted"></i>

                <h5 class="mt-3">
                    No Mess Bills Found
                </h5>

                <p class="text-muted">
                    No mess bills have been generated yet.
                </p>

                <a href="{{ route('mess-bills.create') }}"
                   class="btn btn-primary">

                    <i class="bi bi-plus-circle"></i>
                    Generate Mess Bills

                </a>

            </div>

        @endif

    </div>

</div>

@endsection
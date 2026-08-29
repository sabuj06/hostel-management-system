<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\ActivityLogger;

class PaymentController extends Controller
{
    // Record a payment against an invoice
    public function store(Request $request, Invoice $invoice)
    {
        $data = $request->validate([
            'amount' => [
                'required',
                'numeric',
                'min:0.01',
                'max:' . $invoice->balance()
            ],
            'payment_date' => ['required', 'date'],
            'method' => [
                'required',
                'in:cash,bank_transfer,mobile_banking,card,other'
            ],
            'transaction_ref' => ['nullable', 'string', 'max:255'],
            'remarks' => ['nullable', 'string'],
        ], [
            'amount.max' =>
                'Payment amount cannot exceed the remaining balance of ₹' .
                number_format($invoice->balance(), 2),
        ]);

        // Save invoice old values before payment
        $invoiceOldValues = $invoice->toArray();

        $payment = DB::transaction(function () use (
            $data,
            $invoice,
            $request
        ) {
            $payment = Payment::create([
                ...$data,
                'invoice_id' => $invoice->id,
                'payment_no' =>
                    'PAY-' .
                    now()->format('Ymd') .
                    '-' .
                    Str::upper(Str::random(5)),
                'received_by' => $request->user()->id,
            ]);

            $invoice->refreshPaymentStatus();

            return $payment;
        });

        // Refresh invoice after payment status calculation
        $invoice->refresh();

        /*
        |--------------------------------------------------------------------------
        | Activity Log - Payment Created
        |--------------------------------------------------------------------------
        */

        ActivityLogger::log(
            action: 'created',
            module: 'payments',
            description:
                "Payment {$payment->payment_no} of ₹{$payment->amount} received for invoice {$invoice->invoice_no}. Invoice status: {$invoice->status}",
            subject: $payment,
            newValues: $payment->fresh()->toArray()
        );

        /*
        |--------------------------------------------------------------------------
        | Activity Log - Invoice Payment Status Changed
        |--------------------------------------------------------------------------
        */

        ActivityLogger::log(
            action: 'updated',
            module: 'invoices',
            description:
                "Invoice {$invoice->invoice_no} payment status updated after payment {$payment->payment_no}. Status: {$invoice->status}, Paid: ₹{$invoice->paid_amount}, Balance: ₹{$invoice->balance()}",
            subject: $invoice,
            oldValues: $invoiceOldValues,
            newValues: $invoice->fresh()->toArray()
        );

        if ($request->wantsJson()) {

            return response()->json([
                'success' => true,

                'payment' => $payment->load('receivedBy'),

                'invoice' => [
                    'paid_amount' => $invoice->paid_amount,
                    'balance' => $invoice->balance(),
                    'status' => $invoice->status,
                ],
            ]);
        }

        return back()->with(
            'status',
            'Payment recorded successfully.'
        );
    }

    public function destroy(Payment $payment)
    {
        $invoice = $payment->invoice;

        // Save old payment data before deleting
        $oldPaymentValues = $payment->toArray();

        $paymentNo = $payment->payment_no;
        $paymentAmount = $payment->amount;
        $invoiceNo = $invoice->invoice_no;

        // Save invoice state before removing payment
        $invoiceOldValues = $invoice->toArray();

        $payment->delete();

        // Recalculate invoice payment status
        $invoice->refreshPaymentStatus();
        $invoice->refresh();

        /*
        |--------------------------------------------------------------------------
        | Activity Log - Payment Deleted
        |--------------------------------------------------------------------------
        */

        ActivityLogger::log(
            action: 'deleted',
            module: 'payments',
            description:
                "Payment {$paymentNo} of ₹{$paymentAmount} deleted from invoice {$invoiceNo}",
            subject: $payment,
            oldValues: $oldPaymentValues
        );

        /*
        |--------------------------------------------------------------------------
        | Activity Log - Invoice Updated
        |--------------------------------------------------------------------------
        */

        ActivityLogger::log(
            action: 'updated',
            module: 'invoices',
            description:
                "Invoice {$invoiceNo} payment status updated after payment {$paymentNo} was deleted. Status: {$invoice->status}, Paid: ₹{$invoice->paid_amount}, Balance: ₹{$invoice->balance()}",
            subject: $invoice,
            oldValues: $invoiceOldValues,
            newValues: $invoice->fresh()->toArray()
        );

        if ($request->wantsJson()) {

            return response()->json([
                'success' => true,

                'invoice' => [
                    'paid_amount' => $invoice->paid_amount,
                    'balance' => $invoice->balance(),
                    'status' => $invoice->status,
                ],
            ]);
        }

        return back()->with(
            'status',
            'Payment removed successfully.'
        );
    }
}
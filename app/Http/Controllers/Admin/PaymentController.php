<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    // Record a payment against an invoice (called via AJAX from invoice show page)
    public function store(Request $request, Invoice $invoice)
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01', 'max:' . $invoice->balance()],
            'payment_date' => ['required', 'date'],
            'method' => ['required', 'in:cash,bank_transfer,mobile_banking,card,other'],
            'transaction_ref' => ['nullable', 'string', 'max:255'],
            'remarks' => ['nullable', 'string'],
        ], [
            'amount.max' => 'Payment amount cannot exceed the remaining balance of ₹' . number_format($invoice->balance(), 2),
        ]);

        $payment = DB::transaction(function () use ($data, $invoice, $request) {
            $payment = Payment::create([
                ...$data,
                'invoice_id' => $invoice->id,
                'payment_no' => 'PAY-' . now()->format('Ymd') . '-' . Str::upper(Str::random(5)),
                'received_by' => $request->user()->id,
            ]);

            $invoice->refreshPaymentStatus();

            return $payment;
        });

        if ($request->wantsJson()) {
            $invoice->refresh();

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

        return back()->with('status', 'Payment recorded successfully.');
    }

    public function destroy(Payment $payment)
    {
        $invoice = $payment->invoice;
        $payment->delete();
        $invoice->refreshPaymentStatus();

        if (request()->wantsJson()) {
            $invoice->refresh();

            return response()->json([
                'success' => true,
                'invoice' => [
                    'paid_amount' => $invoice->paid_amount,
                    'balance' => $invoice->balance(),
                    'status' => $invoice->status,
                ],
            ]);
        }

        return back()->with('status', 'Payment removed successfully.');
    }
}
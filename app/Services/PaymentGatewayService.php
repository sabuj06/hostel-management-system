<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentGatewayOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Wraps Razorpay's Orders API so the rest of the app never talks to the
 * gateway directly. Swapping to Cashfree or PhonePe later means rewriting
 * only this class — createOrder()/verifyPayment()/verifyWebhookSignature()
 * — everything else (controllers, views, invoice logic) stays the same.
 *
 * Requires: composer require razorpay/razorpay
 */
class PaymentGatewayService
{
    private function client(): \Razorpay\Api\Api
    {
        if (! class_exists(\Razorpay\Api\Api::class)) {
            throw new \RuntimeException('Razorpay SDK not installed. Run: composer require razorpay/razorpay');
        }

        return new \Razorpay\Api\Api(
            config('services.razorpay.key_id'),
            config('services.razorpay.key_secret')
        );
    }

    // Step 1: create a Razorpay Order for the invoice's outstanding balance
    public function createOrder(Invoice $invoice): PaymentGatewayOrder
    {
        $amount = $invoice->balance();

        if ($amount <= 0) {
            throw new \RuntimeException('This invoice has no outstanding balance to pay.');
        }

        $razorpayOrder = $this->client()->order->create([
            // Razorpay expects the smallest currency unit (paise for INR)
            'amount' => (int) round($amount * 100),
            'currency' => 'INR',
            'receipt' => $invoice->invoice_no,
            'notes' => [
                'invoice_id' => (string) $invoice->id,
                'student_id' => (string) $invoice->student_id,
            ],
        ]);

        return PaymentGatewayOrder::create([
            'invoice_id' => $invoice->id,
            'student_id' => $invoice->student_id,
            'gateway' => 'razorpay',
            'gateway_order_id' => $razorpayOrder['id'],
            'amount' => $amount,
            'currency' => 'INR',
            'status' => 'created',
        ]);
    }

    // Step 2: called from the client-side success callback — verifies the
    // cryptographic signature before trusting the payment actually happened.
    public function verifyPayment(string $orderId, string $paymentId, string $signature): PaymentGatewayOrder
    {
        $order = PaymentGatewayOrder::where('gateway_order_id', $orderId)->firstOrFail();

        if ($order->status === 'paid') {
            return $order; // idempotent — already confirmed, possibly by the webhook
        }

        $generatedSignature = hash_hmac(
            'sha256',
            $orderId . '|' . $paymentId,
            config('services.razorpay.key_secret')
        );

        if (! hash_equals($generatedSignature, $signature)) {
            $order->update(['status' => 'failed', 'failure_reason' => 'Signature verification failed.']);
            throw new \RuntimeException('Payment signature verification failed. If money was deducted, contact the hostel office.');
        }

        $this->markPaid($order, $paymentId);

        return $order->fresh();
    }

    // Called from the webhook handler as a safety net — confirms payment even
    // if the student closed the browser before the client-side callback fired.
    public function markPaidFromWebhook(string $orderId, string $paymentId): void
    {
        $order = PaymentGatewayOrder::where('gateway_order_id', $orderId)->first();

        if (! $order || $order->status === 'paid') {
            return; // unknown order, or already recorded — nothing to do
        }

        $this->markPaid($order, $paymentId);
    }

    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        $expected = hash_hmac('sha256', $payload, config('services.razorpay.webhook_secret'));

        return hash_equals($expected, $signature);
    }

    private function markPaid(PaymentGatewayOrder $order, string $paymentId): void
    {
        DB::transaction(function () use ($order, $paymentId) {
            $order->update([
                'status' => 'paid',
                'gateway_payment_id' => $paymentId,
            ]);

            $invoice = $order->invoice;

            Payment::create([
                'invoice_id' => $invoice->id,
                'payment_no' => 'PAY-' . now()->format('Ymd') . '-' . Str::upper(Str::random(5)),
                'amount' => $order->amount,
                'payment_date' => now(),
                'method' => 'card', // online gateway payment
                'transaction_ref' => $paymentId,
                'remarks' => "Paid online via Razorpay (order {$order->gateway_order_id}).",
            ]);

            $invoice->refreshPaymentStatus();
        });
    }
}
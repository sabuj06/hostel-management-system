<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentGatewayOrder extends Model
{
    protected $fillable = [
        'invoice_id', 'student_id', 'gateway', 'gateway_order_id', 'gateway_payment_id',
        'amount', 'currency', 'status', 'failure_reason',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
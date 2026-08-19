<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    protected $fillable = [
        'invoice_no', 'student_id', 'fee_structure_id', 'period', 'amount',
        'paid_amount', 'due_date', 'status', 'generated_by', 'remarks',
    ];

    protected function casts(): array
    {
        return ['due_date' => 'date'];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function feeStructure(): BelongsTo
    {
        return $this->belongsTo(FeeStructure::class);
    }

    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function balance(): float
    {
        return round($this->amount - $this->paid_amount, 2);
    }

    // Recompute paid_amount + status from the payments table.
    // Call this after any payment is added/removed.
    public function refreshPaymentStatus(): void
    {
        $paid = $this->payments()->sum('amount');
        $status = 'unpaid';

        if ($paid >= $this->amount) {
            $status = 'paid';
        } elseif ($paid > 0) {
            $status = 'partial';
        } elseif ($this->due_date->isPast()) {
            $status = 'overdue';
        }

        $this->update(['paid_amount' => $paid, 'status' => $status]);
    }
}
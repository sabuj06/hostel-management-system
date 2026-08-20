<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Visitor extends Model
{
    protected $fillable = [
        'gate_pass_no', 'student_id', 'visitor_name', 'phone', 'relation', 'purpose',
        'id_proof_type', 'id_proof_number', 'total_visitors',
        'check_in_time', 'check_out_time', 'status', 'approved_by', 'remarks',
    ];

    protected function casts(): array
    {
        return [
            'check_in_time' => 'datetime',
            'check_out_time' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function scopeCurrentlyIn($query)
    {
        return $query->where('status', 'checked_in');
    }

    public function durationInMinutes(): ?int
    {
        if (! $this->check_out_time) {
            return null;
        }

        return $this->check_in_time->diffInMinutes($this->check_out_time);
    }
}
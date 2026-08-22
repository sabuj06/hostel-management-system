<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MessCut extends Model
{
    protected $fillable = ['student_id', 'from_date', 'to_date', 'reason', 'marked_by'];

    protected function casts(): array
    {
        return [
            'from_date' => 'date',
            'to_date' => 'date',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function markedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'marked_by');
    }

    // Number of days covered by this mess cut (inclusive)
    public function dayCount(): int
    {
        return $this->from_date->diffInDays($this->to_date) + 1;
    }

    public function coversDate(\DateTimeInterface $date): bool
    {
        return $date >= $this->from_date && $date <= $this->to_date;
    }
}
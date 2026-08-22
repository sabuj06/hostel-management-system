<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MessRate extends Model
{
    protected $fillable = ['hostel_id', 'rate_per_day'];

    public function hostel(): BelongsTo
    {
        return $this->belongsTo(Hostel::class);
    }
}
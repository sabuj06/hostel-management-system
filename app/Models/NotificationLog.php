<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class NotificationLog extends Model
{
    protected $fillable = [
        'channel', 'recipient', 'subject', 'message', 'status', 'error',
        'related_type', 'related_id', 'triggered_by',
    ];

    public function triggeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'triggered_by');
    }

    public function related(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'related_type', 'related_id');
    }
}
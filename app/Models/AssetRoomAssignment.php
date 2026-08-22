<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetRoomAssignment extends Model
{
    protected $fillable = ['asset_id', 'room_id', 'quantity', 'assigned_date', 'condition', 'notes'];

    protected function casts(): array
    {
        return ['assigned_date' => 'date'];
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }
}
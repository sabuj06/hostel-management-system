<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Room extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'floor_id', 'room_number', 'room_type', 'capacity', 'monthly_rent', 'status',
    ];

    public function floor(): BelongsTo
    {
        return $this->belongsTo(Floor::class);
    }

    public function beds(): HasMany
    {
        return $this->hasMany(Bed::class);
    }

    public function availableBedsCount(): int
    {
        return $this->beds()->where('status', 'available')->count();
    }

    public function occupiedBedsCount(): int
    {
        return $this->beds()->where('status', 'occupied')->count();
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MealMenu extends Model
{
    protected $fillable = ['hostel_id', 'day_of_week', 'meal_type', 'items'];

    public function hostel(): BelongsTo
    {
        return $this->belongsTo(Hostel::class);
    }

    public const DAYS = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
    public const MEAL_TYPES = ['breakfast', 'lunch', 'dinner'];
}
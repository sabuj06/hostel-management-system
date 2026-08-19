<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Hostel extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'type', 'address', 'warden_name', 'contact_number', 'status',
    ];

    public function blocks(): HasMany
    {
        return $this->hasMany(Block::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
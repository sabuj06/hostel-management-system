<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Notice extends Model
{
    protected $fillable = [
        'title', 'body', 'audience', 'hostel_id', 'priority',
        'posted_by', 'publish_date', 'expiry_date', 'status',
    ];

    protected function casts(): array
    {
        return [
            'publish_date' => 'date',
            'expiry_date' => 'date',
        ];
    }

    public function hostel(): BelongsTo
    {
        return $this->belongsTo(Hostel::class);
    }

    public function postedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    public function reads(): HasMany
    {
        return $this->hasMany(NoticeRead::class);
    }

    public function isReadBy(int $userId): bool
    {
        return $this->reads()->where('user_id', $userId)->exists();
    }

    public function isExpired(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->where(fn ($q) => $q->whereNull('expiry_date')->orWhere('expiry_date', '>=', now()));
    }
}
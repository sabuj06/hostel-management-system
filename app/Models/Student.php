<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'student_uid', 'name', 'email', 'phone', 'gender',
        'date_of_birth', 'course', 'department', 'session', 'address',
        'photo_path', 'document_path', 'admission_date', 'status',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'admission_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function guardians(): HasMany
    {
        return $this->hasMany(Guardian::class);
    }

    public function primaryGuardian(): ?Guardian
    {
        return $this->guardians()->where('is_primary', true)->first()
            ?? $this->guardians()->first();
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(RoomAllocation::class);
    }

    // The bed/room the student currently occupies, if any
    public function currentAllocation(): ?RoomAllocation
    {
        return $this->allocations()->where('status', 'active')->latest()->first();
    }

    public function scopeUnallocated($query)
    {
        return $query->whereDoesntHave('allocations', fn ($q) => $q->where('status', 'active'));
    }
}
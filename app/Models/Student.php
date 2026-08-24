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
        'user_id',
        'student_uid',
        'name',
        'email',
        'phone',
        'gender',
        'date_of_birth',
        'course',
        'department',
        'session',
        'address',
        'photo_path',
        'document_path',
        'admission_date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'admission_date' => 'date',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | User
    |--------------------------------------------------------------------------
    */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Guardians
    |--------------------------------------------------------------------------
    */

    public function guardians(): HasMany
    {
        return $this->hasMany(Guardian::class);
    }

    public function primaryGuardian(): ?Guardian
    {
        return $this->guardians()->where('is_primary', true)->first()
            ?? $this->guardians()->first();
    }

    /*
    |--------------------------------------------------------------------------
    | Room Allocations
    |--------------------------------------------------------------------------
    */

    public function allocations(): HasMany
    {
        return $this->hasMany(RoomAllocation::class);
    }

    // The bed/room the student currently occupies, if any
    public function currentAllocation(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(RoomAllocation::class)
            ->where('status', 'active')
            ->latestOfMany();
    }

    public function scopeUnallocated($query)
    {
        return $query->whereDoesntHave(
            'allocations',
            fn ($q) => $q->where('status', 'active')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Complaints
    |--------------------------------------------------------------------------
    */

    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Invoices
    |--------------------------------------------------------------------------
    */

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Mess Cuts
    |--------------------------------------------------------------------------
    */

    public function messCuts(): HasMany
    {
        return $this->hasMany(MessCut::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Chat Messages
    |--------------------------------------------------------------------------
    */

    public function chatMessages(): HasMany
    {
        return $this->hasMany(ChatMessage::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Attendance
    |--------------------------------------------------------------------------
    */

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    // Convenience relation used with eager-load constraints
    public function attendanceOn(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Leave Requests
    |--------------------------------------------------------------------------
    */

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Student Documents
    |--------------------------------------------------------------------------
    |
    | NID, Birth Certificate, Photo etc.
    |
    */

    public function documents(): HasMany
    {
        return $this->hasMany(StudentDocument::class);
    }
}
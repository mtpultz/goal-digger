<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Buddy extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'buddy_id',
        'status',
        'accepted_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'accepted_at' => 'datetime',
    ];

    /**
     * Get the user that initiated the buddy request.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user that was invited to be a buddy.
     */
    public function buddy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buddy_id');
    }

    /**
     * Scope a query to only include pending buddy requests.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'PENDING');
    }

    /**
     * Scope a query to only include accepted buddy requests.
     */
    public function scopeAccepted($query)
    {
        return $query->where('status', 'ACCEPTED');
    }

    /**
     * Scope a query to only include rejected buddy requests.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'REJECTED');
    }

    /**
     * Accept the buddy request.
     */
    public function accept(): void
    {
        $this->update([
            'status' => 'ACCEPTED',
            'accepted_at' => now(),
        ]);
    }

    /**
     * Reject the buddy request.
     */
    public function reject(): void
    {
        $this->update([
            'status' => 'REJECTED',
        ]);
    }

    /**
     * Check if the buddy request is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'PENDING';
    }

    /**
     * Check if the buddy request is accepted.
     */
    public function isAccepted(): bool
    {
        return $this->status === 'ACCEPTED';
    }

    /**
     * Check if the buddy request is rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === 'REJECTED';
    }
}

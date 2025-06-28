<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BuddyGoal extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'goal_id',
        'user_id',
        'buddy_id',
        'status',
        'role',
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
     * Get the goal that the buddy is invited to.
     */
    public function goal(): BelongsTo
    {
        return $this->belongsTo(Goal::class);
    }

    /**
     * Get the user that initiated the invitation.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the buddy that was invited.
     */
    public function buddy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buddy_id');
    }

    /**
     * Scope a query to only include pending invitations.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'PENDING');
    }

    /**
     * Scope a query to only include accepted invitations.
     */
    public function scopeAccepted($query)
    {
        return $query->where('status', 'ACCEPTED');
    }

    /**
     * Scope a query to only include rejected invitations.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'REJECTED');
    }

    /**
     * Accept the invitation.
     */
    public function accept(): void
    {
        $this->update([
            'status' => 'ACCEPTED',
            'accepted_at' => now(),
        ]);
    }

    /**
     * Reject the invitation.
     */
    public function reject(): void
    {
        $this->update([
            'status' => 'REJECTED',
        ]);
    }

    /**
     * Check if the invitation is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'PENDING';
    }

    /**
     * Check if the invitation is accepted.
     */
    public function isAccepted(): bool
    {
        return $this->status === 'ACCEPTED';
    }

    /**
     * Check if the invitation is rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === 'REJECTED';
    }

    /**
     * Check if the buddy has viewer role.
     */
    public function isViewer(): bool
    {
        return $this->role === 'VIEWER';
    }

    /**
     * Check if the buddy has contributor role.
     */
    public function isContributor(): bool
    {
        return $this->role === 'CONTRIBUTOR';
    }

    /**
     * Check if the buddy has collaborator role.
     */
    public function isCollaborator(): bool
    {
        return $this->role === 'COLLABORATOR';
    }
}

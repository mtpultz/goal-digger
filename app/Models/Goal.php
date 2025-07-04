<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Goal extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'parent_id',
        'root_id',
        'user_id',
        'title',
        'description',
        'due_date',
        'status',
        'links',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'due_date' => 'datetime',
        'links' => 'array',
    ];

    /**
     * Get the user that owns the goal.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the parent goal.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Goal::class, 'parent_id');
    }

    /**
     * Get the root goal.
     */
    public function root(): BelongsTo
    {
        return $this->belongsTo(Goal::class, 'root_id');
    }

    /**
     * Get the child goals.
     */
    public function children(): HasMany
    {
        return $this->hasMany(Goal::class, 'parent_id');
    }

    /**
     * Get all descendants of the goal.
     */
    public function descendants()
    {
        return $this->children()->with('descendants');
    }

    /**
     * Get all ancestors of the goal.
     */
    public function ancestors()
    {
        return $this->parent()->with('ancestors');
    }

    /**
     * Get the comments for the goal.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Get the root comments for the goal.
     */
    public function rootComments(): HasMany
    {
        return $this->comments()->whereNull('parent_id');
    }

    /**
     * Get all buddy goals for this goal.
     */
    public function buddyGoals(): HasMany
    {
        return $this->hasMany(BuddyGoal::class);
    }

    /**
     * Get all accepted buddy goals for this goal.
     */
    public function acceptedBuddyGoals(): HasMany
    {
        return $this->buddyGoals()->where('status', 'ACCEPTED');
    }

    /**
     * Get all pending buddy goals for this goal.
     */
    public function pendingBuddyGoals(): HasMany
    {
        return $this->buddyGoals()->where('status', 'PENDING');
    }

    /**
     * Get all buddies with access to this goal.
     */
    public function buddies()
    {
        return $this->acceptedBuddyGoals()->with('buddy');
    }

    /**
     * Scope a query to only include root goals.
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope a query to only include goals with a specific status.
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Check if the goal is a root goal.
     */
    public function isRoot(): bool
    {
        return is_null($this->parent_id);
    }

    /**
     * Check if the goal is a leaf goal (has no children).
     */
    public function isLeaf(): bool
    {
        return $this->children()->count() === 0;
    }

    /**
     * Get the depth of the goal in the tree.
     */
    public function getDepth(): int
    {
        if ($this->isRoot()) {
            return 0;
        }

        return $this->parent->getDepth() + 1;
    }
}

<?php

namespace App\Models;

use App\Notifications\ResetPasswordNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\Contracts\OAuthenticatable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable implements OAuthenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the goals for the user.
     */
    public function goals(): HasMany
    {
        return $this->hasMany(Goal::class);
    }

    /**
     * Get the comments for the user.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Get the buddy requests initiated by the user.
     */
    public function buddyRequests(): HasMany
    {
        return $this->hasMany(Buddy::class);
    }

    /**
     * Get the buddy requests received by the user.
     */
    public function receivedBuddyRequests(): HasMany
    {
        return $this->hasMany(Buddy::class, 'buddy_id');
    }

    /**
     * Get all accepted buddies of the user.
     */
    public function buddies()
    {
        return $this->hasMany(Buddy::class)
            ->where('status', 'ACCEPTED')
            ->with('buddy');
    }

    /**
     * Get all users who have accepted this user as a buddy.
     */
    public function buddyOf()
    {
        return $this->hasMany(Buddy::class, 'buddy_id')
            ->where('status', 'ACCEPTED')
            ->with('user');
    }

    /**
     * Get all pending buddy requests sent by the user.
     */
    public function pendingBuddyRequests()
    {
        return $this->hasMany(Buddy::class)
            ->where('status', 'PENDING')
            ->with('buddy');
    }

    /**
     * Get all pending buddy requests received by the user.
     */
    public function receivedPendingBuddyRequests()
    {
        return $this->hasMany(Buddy::class, 'buddy_id')
            ->where('status', 'PENDING')
            ->with('user');
    }

    /**
     * Send the password reset notification.
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}

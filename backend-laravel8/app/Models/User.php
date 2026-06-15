<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    const ROLE_USER  = 0;
    const ROLE_ADMIN = 1;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'role'              => 'integer',
    ];

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    // ─── Relationships ────────────────────────────────────────────────────────

    public function subscriptions()
    {
        return $this->hasMany(\App\Models\Subscription::class);
    }

    public function activeSubscription()
    {
        $sub = $this->subscriptions()
            ->where('status', \App\Models\Subscription::STATUS_ACTIVE)
            ->latest()
            ->first();

        if ($sub && $sub->current_period_end && $sub->current_period_end->isPast()) {
            $sub->status = \App\Models\Subscription::STATUS_EXPIRED;
            $sub->save();
            return null;
        }

        return $sub;
    }

    public function toolLimits()
    {
        return $this->hasMany(\App\Models\UserToolLimit::class);
    }
}

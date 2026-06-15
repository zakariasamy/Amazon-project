<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class UserToolLimit extends Model
{
    use HasFactory;

    protected $fillable = [
        'subscription_id', 'user_id', 'tool_name',
        'limit_count', 'bonus_count', 'used_count', 'next_reset_at',
    ];

    protected $casts = [
        'next_reset_at' => 'datetime',
    ];

    // ─── Relationships ───────────────────────────────────────────────────────

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    /**
     * Lazy monthly reset: if the reset period has passed, reset usage.
     */
    public function maybeLazyReset(): void
    {
        if ($this->next_reset_at && Carbon::now()->gt($this->next_reset_at)) {
            $this->used_count   = 0;
            $this->next_reset_at = $this->next_reset_at->addMonth();
            $this->save();
        }
    }

    /**
     * Returns true if this tool is unlimited (-1).
     */
    public function isUnlimited(): bool
    {
        return $this->limit_count === -1;
    }

    /**
     * Returns remaining uses (limit_count + bonus_count - used_count),
     * or PHP_INT_MAX when unlimited.
     */
    public function remaining(): int
    {
        if ($this->isUnlimited()) {
            return PHP_INT_MAX;
        }
        return max(0, $this->limit_count + $this->bonus_count - $this->used_count);
    }

    /**
     * Returns true if the user still has remaining uses.
     */
    public function hasRemaining(): bool
    {
        $this->maybeLazyReset();
        return $this->isUnlimited() || $this->remaining() > 0;
    }

    /**
     * Increment the used_count by 1.
     */
    public function consume(): void
    {
        $this->increment('used_count');
    }
}

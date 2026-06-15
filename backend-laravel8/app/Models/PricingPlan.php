<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class PricingPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'description', 'price', 'billing_cycle', 'trial_days',
        'limits', 'is_active', 'is_featured', 'sort_order',
        'promo_price', 'promo_start_at', 'promo_end_at',
    ];

    protected $casts = [
        'limits'         => 'array',
        'is_active'      => 'boolean',
        'is_featured'    => 'boolean',
        'price'          => 'decimal:2',
        'promo_price'    => 'decimal:2',
        'trial_days'     => 'integer',
        'promo_start_at' => 'datetime',
        'promo_end_at'   => 'datetime',
    ];

    // ─── Relationships ───────────────────────────────────────────────────────

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    // ─── Scopes ──────────────────────────────────────────────────────────────

    /**
     * Only return active plans, ordered by sort_order.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    /**
     * Returns true if the plan is currently in a promo window.
     */
    public function isOnPromo(): bool
    {
        if (is_null($this->promo_price)) {
            return false;
        }
        $now = Carbon::now();
        $start = $this->promo_start_at;
        $end   = $this->promo_end_at;

        if ($start && $end) {
            return $now->between($start, $end);
        }
        if ($start) {
            return $now->gte($start);
        }
        return true; // promo_price set, no date range => always active
    }

    /**
     * Returns the effective display price (promo if active, else base).
     */
    public function effectivePrice(): float
    {
        return $this->isOnPromo() ? (float) $this->promo_price : (float) $this->price;
    }

    /**
     * Returns a human-friendly label for a limit value.
     * -1 means "Unlimited".
     */
    public static function formatLimit($value): string
    {
        return ($value === -1 || $value === null) ? 'Unlimited' : number_format($value) . '/mo';
    }

    /**
     * Get limit for a specific tool, or -1 if not set (unlimited).
     */
    public function getLimitFor(string $tool): int
    {
        $limits = $this->limits ?? [];
        return isset($limits[$tool]) ? (int) $limits[$tool] : -1;
    }

    /**
     * Returns true if this is a free plan (price = 0).
     */
    public function isFree(): bool
    {
        return $this->price == 0;
    }
}

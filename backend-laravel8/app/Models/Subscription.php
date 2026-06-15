<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Subscription extends Model
{
    use HasFactory;

    const STATUS_ACTIVE           = 'active';
    const STATUS_PENDING_APPROVAL = 'pending_approval';
    const STATUS_EXPIRED          = 'expired';
    const STATUS_REJECTED         = 'rejected';

    protected $fillable = [
        'user_id', 'pricing_plan_id', 'status',
        'current_period_start', 'current_period_end',
    ];

    protected $casts = [
        'current_period_start' => 'datetime',
        'current_period_end'   => 'datetime',
    ];

    // ─── Relationships ───────────────────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plan()
    {
        return $this->belongsTo(PricingPlan::class, 'pricing_plan_id');
    }

    public function toolLimits()
    {
        return $this->hasMany(UserToolLimit::class);
    }

    public function paymentProofs()
    {
        return $this->hasMany(PaymentProof::class);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING_APPROVAL;
    }

    /**
     * Activate the subscription and initialise tool limits from the plan.
     * If an upgrade proof exists with carry-over metadata, applies adjusted limits.
     */
    public function activate(): void
    {
        $now  = Carbon::now();
        $plan = $this->plan;

        $this->status               = self::STATUS_ACTIVE;
        $this->current_period_start = $now;

        // Yearly plans: subscription expires in 1 year
        // BUT per PRICING_PLAN.md §3: limits reset monthly regardless (Helium 10 standard)
        $this->current_period_end = $plan->billing_cycle === 'yearly'
            ? $now->copy()->addYear()
            : $now->copy()->addMonth();
        $this->save();

        // Check if there is carry-over data from an upgrade proof
        $carryOver = [];
        $upgradedProof = $this->paymentProofs()
            ->where('admin_notes', 'like', 'UPGRADE%')
            ->latest()
            ->first();

        if ($upgradedProof) {
            // Parse: "UPGRADE from plan #X. Carry-over: {...}"
            preg_match('/Carry-over: (.+)$/', $upgradedProof->admin_notes, $m);
            if (!empty($m[1])) {
                $carryOver = json_decode($m[1], true) ?? [];
            }
        }

        // Create / reset tool limits from plan (always reset monthly)
        $limits    = $plan->limits ?? [];
        $nextReset = $now->copy()->addMonth(); // Monthly reset regardless of billing_cycle

        foreach ($limits as $tool => $count) {
            $count = (int) $count;

            // Apply carry-over if this is an upgrade
            if (!empty($carryOver[$tool])) {
                $count = (int) $carryOver[$tool]['new_limit'];
            }

            UserToolLimit::updateOrCreate(
                ['subscription_id' => $this->id, 'tool_name' => $tool],
                [
                    'user_id'       => $this->user_id,
                    'limit_count'   => $count,
                    'bonus_count'   => 0,
                    'used_count'    => 0,
                    'next_reset_at' => $nextReset,
                ]
            );
        }

        // Expire the old active subscription (if this was an upgrade)
        if ($upgradedProof) {
            // Find the old active subscription for this user (not this one)
            \App\Models\Subscription::where('user_id', $this->user_id)
                ->where('id', '<>', $this->id)
                ->where('status', self::STATUS_ACTIVE)
                ->update(['status' => self::STATUS_EXPIRED]);
        }
    }

}

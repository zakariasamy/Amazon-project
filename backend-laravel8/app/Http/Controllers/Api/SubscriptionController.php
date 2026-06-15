<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\PricingPlan;
use App\Models\PaymentProof;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SubscriptionController extends Controller
{
    /**
     * GET /api/subscription/status
     * Returns the authenticated user's active plan limits and remaining usage.
     */
    public function status(Request $request)
    {
        $user = $request->user();
        $sub  = $user->activeSubscription();

        if (!$sub) {
            return response()->json([
                'plan'   => null,
                'status' => 'none',
                'limits' => [],
            ]);
        }

        $limits = [];
        foreach ($sub->toolLimits as $tl) {
            $tl->maybeLazyReset();
            $limits[$tl->tool_name] = [
                'limit'     => $tl->isUnlimited() ? 'unlimited' : ($tl->limit_count + $tl->bonus_count),
                'used'      => $tl->used_count,
                'remaining' => $tl->isUnlimited() ? 'unlimited' : $tl->remaining(),
            ];
        }

        return response()->json([
            'plan'   => $sub->plan->name,
            'status' => $sub->status,
            'period_end' => $sub->current_period_end,
            'limits' => $limits,
        ]);
    }

    /**
     * POST /api/subscription/payment-proof
     * Handles plan selection + InstaPay screenshot upload.
     */
    public function uploadProof(Request $request)
    {
        $request->validate([
            'plan_id'     => 'required|exists:pricing_plans,id',
            'proof_image' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $user = $request->user();
        $plan = PricingPlan::findOrFail($request->plan_id);

        if ($plan->isFree()) {
            return response()->json(['error' => 'Free plans do not require payment proof.'], 422);
        }

        // Store image
        $path = $request->file('proof_image')->store('payment-proofs', 'public');

        // Cancel any existing pending subscription (user resubmitting)
        $user->subscriptions()
            ->where('status', Subscription::STATUS_PENDING_APPROVAL)
            ->update(['status' => Subscription::STATUS_REJECTED]);

        // Create pending subscription
        $sub = Subscription::create([
            'user_id'         => $user->id,
            'pricing_plan_id' => $plan->id,
            'status'          => Subscription::STATUS_PENDING_APPROVAL,
        ]);

        // Attach proof
        PaymentProof::create([
            'subscription_id' => $sub->id,
            'proof_image_path'=> $path,
            'status'          => PaymentProof::STATUS_PENDING,
        ]);

        return response()->json([
            'message' => 'Payment proof submitted. Your subscription will be activated after admin review.',
            'subscription_id' => $sub->id,
        ], 201);
    }

    /**
     * POST /api/subscription/upgrade
     * Upgrades an active subscription to a new plan with carry-over of remaining limits.
     * Carry-over: New Limit = New Plan Limit + (Old Plan Limit - Old Used)
     */
    public function upgrade(Request $request)
    {
        $request->validate([
            'plan_id'     => 'required|exists:pricing_plans,id',
            'proof_image' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $user    = $request->user();
        $newPlan = PricingPlan::findOrFail($request->plan_id);
        $oldSub  = $user->activeSubscription();

        if (!$oldSub) {
            // No active subscription — treat as a new sign-up
            return $this->uploadProof($request);
        }

        // Capture old limits before creating the new subscription
        $oldLimits = $oldSub->toolLimits->keyBy('tool_name');

        // Upload proof
        $path = $request->file('proof_image')->store('payment-proofs', 'public');

        // Create new pending subscription for upgrade
        $newSub = Subscription::create([
            'user_id'         => $user->id,
            'pricing_plan_id' => $newPlan->id,
            'status'          => Subscription::STATUS_PENDING_APPROVAL,
            // Store carry-over data as JSON in admin_notes of proof for activation
        ]);

        // Store carry-over metadata in the proof record so admin can reference it
        $carryOver = [];
        foreach (($newPlan->limits ?? []) as $tool => $newLimit) {
            $newLimit   = (int) $newLimit;
            $oldLimit   = $oldLimits->has($tool) ? (int) $oldLimits[$tool]->limit_count : 0;
            $oldUsed    = $oldLimits->has($tool) ? (int) $oldLimits[$tool]->used_count  : 0;
            $remaining  = max(0, $oldLimit - $oldUsed);
            $adjustedLimit = ($newLimit === -1) ? -1 : $newLimit + $remaining;
            $carryOver[$tool] = [
                'old_remaining' => $remaining,
                'new_limit'     => $adjustedLimit,
            ];
        }

        PaymentProof::create([
            'subscription_id' => $newSub->id,
            'proof_image_path'=> $path,
            'status'          => PaymentProof::STATUS_PENDING,
            'admin_notes'     => 'UPGRADE from plan #' . $oldSub->pricing_plan_id . '. Carry-over: ' . json_encode($carryOver),
        ]);

        return response()->json([
            'message'         => 'Upgrade request submitted. Your new plan will be activated after admin review.',
            'subscription_id' => $newSub->id,
            'carry_over'      => $carryOver,
        ], 201);
    }

    /**
     * GET /api/subscription/instapay-info
     * Returns the InstaPay payment instructions (phone/username) from app settings.
     */
    public function instapayInfo()
    {
        $info = \DB::table('app_settings')
            ->whereIn('key', ['instapay_username', 'instapay_phone', 'instapay_instructions'])
            ->get()
            ->keyBy('key');

        return response()->json([
            'username'     => $info->get('instapay_username')?->value ?? 'amazon.analyzer@instapay',
            'phone'        => $info->get('instapay_phone')?->value ?? null,
            'instructions' => $info->get('instapay_instructions')?->value ?? 'Send the exact amount to the InstaPay username above, then upload a screenshot of your payment.',
        ]);
    }

    /**
     * GET /api/pricing-plans
     * Public endpoint – returns active plans for front-end use.
     */

    public function publicPlans()
    {
        $plans = PricingPlan::active()->get()->map(function ($plan) {
            return [
                'id'              => $plan->id,
                'name'            => $plan->name,
                'slug'            => $plan->slug,
                'description'     => $plan->description,
                'price'           => $plan->price,
                'effective_price' => $plan->effectivePrice(),
                'is_promo'        => $plan->isOnPromo(),
                'promo_price'     => $plan->promo_price,
                'billing_cycle'   => $plan->billing_cycle,
                'is_featured'     => $plan->is_featured,
                'is_free'         => $plan->isFree(),
                'limits'          => $plan->limits,
            ];
        });

        return response()->json($plans);
    }
}

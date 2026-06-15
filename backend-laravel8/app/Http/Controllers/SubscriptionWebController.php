<?php

namespace App\Http\Controllers;

use App\Models\PricingPlan;
use App\Models\Subscription;
use App\Models\PaymentProof;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SubscriptionWebController extends Controller
{
    /**
     * Display the list of pricing plans for upgrades.
     */
    public function upgrade()
    {
        $plans = PricingPlan::where('slug', '<>', 'free')
            ->where('is_active', true)
            ->orderBy('price', 'asc')
            ->get();

        return view('subscription.upgrade', compact('plans'));
    }

    /**
     * Display the payment page with InstaPay credentials.
     */
    public function pay($plan_id)
    {
        $plan = PricingPlan::findOrFail($plan_id);

        if ($plan->isFree()) {
            return redirect('/dashboard')->with('success', 'You are already on the Free Plan.');
        }

        // Fetch InstaPay settings
        $settings = DB::table('app_settings')
            ->whereIn('key', ['instapay_username', 'instapay_phone', 'instapay_instructions'])
            ->get()
            ->keyBy('key');

        $instapay = [
            'username'     => $settings->get('instapay_username')?->value ?? 'amazon.analyzer@instapay',
            'phone'        => $settings->get('instapay_phone')?->value ?? '',
            'instructions' => $settings->get('instapay_instructions')?->value ?? 'Send the exact subscription amount to the InstaPay username above, then upload a clear screenshot of the payment confirmation.',
        ];

        return view('subscription.pay', compact('plan', 'instapay'));
    }

    /**
     * Submit payment proof and request activation/upgrade.
     */
    public function submitPay(Request $request)
    {
        $request->validate([
            'plan_id'     => 'required|exists:pricing_plans,id',
            'proof_image' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $user = Auth::user();
        $plan = PricingPlan::findOrFail($request->plan_id);

        if ($plan->isFree()) {
            return back()->withErrors(['error' => 'Free plans do not require payment.']);
        }

        // Store screenshot proof
        $path = $request->file('proof_image')->store('payment-proofs', 'public');

        // Check if there is an active subscription to upgrade
        $oldSub = $user->activeSubscription();
        $adminNotes = null;

        if ($oldSub) {
            $oldLimits = $oldSub->toolLimits->keyBy('tool_name');
            $carryOver = [];

            foreach (($plan->limits ?? []) as $tool => $newLimit) {
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

            $adminNotes = 'UPGRADE from plan #' . $oldSub->pricing_plan_id . '. Carry-over: ' . json_encode($carryOver);
        }

        // Reject other pending requests
        $user->subscriptions()
            ->where('status', Subscription::STATUS_PENDING_APPROVAL)
            ->update(['status' => Subscription::STATUS_REJECTED]);

        // Create pending subscription
        $sub = Subscription::create([
            'user_id'         => $user->id,
            'pricing_plan_id' => $plan->id,
            'status'          => Subscription::STATUS_PENDING_APPROVAL,
        ]);

        // Save proof
        PaymentProof::create([
            'subscription_id'  => $sub->id,
            'proof_image_path' => $path,
            'status'           => PaymentProof::STATUS_PENDING,
            'admin_notes'      => $adminNotes,
        ]);

        return redirect()->route('dashboard')->with('success', 'Payment proof submitted successfully! Your plan will be activated once the admin reviews it.');
    }
}

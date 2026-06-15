<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PricingPlan;
use App\Models\Subscription;
use App\Models\PaymentProof;
use App\Models\UserToolLimit;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PricingAdminController extends Controller
{
    public function updateInstapay(Request $request)
    {
        $request->validate([
            'instapay_username'     => 'required|string|max:100',
            'instapay_phone'        => 'nullable|string|max:20',
            'instapay_instructions' => 'required|string|max:500',
        ]);

        $now = now();
        $keys = ['instapay_username', 'instapay_phone', 'instapay_instructions'];

        foreach ($keys as $key) {
            \DB::table('app_settings')->updateOrInsert(
                ['key' => $key],
                [
                    'value'      => $request->input($key, ''),
                    'type'       => 'string',
                    'category'   => 'payments',
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }

        return back()->with('status', 'InstaPay settings saved successfully.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Pricing Plans CRUD
    // ─────────────────────────────────────────────────────────────────────────

    public function index()
    {
        $plans = PricingPlan::orderBy('sort_order')->get();
        return view('admin.pricing.index', compact('plans'));
    }

    public function create()
    {
        return view('admin.pricing.form', ['plan' => null]);
    }

    public function store(Request $request)
    {
        $data = $this->validatePlan($request);
        $data['slug'] = Str::slug($data['name']);
        $data['limits'] = $this->buildLimits($request);

        PricingPlan::create($data);
        return redirect()->route('admin.pricing.index')->with('success', 'Plan created successfully.');
    }

    public function edit($id)
    {
        $plan = PricingPlan::findOrFail($id);
        return view('admin.pricing.form', compact('plan'));
    }

    public function update(Request $request, $id)
    {
        $plan = PricingPlan::findOrFail($id);
        $data = $this->validatePlan($request);
        $data['slug'] = Str::slug($data['name']);
        $data['limits'] = $this->buildLimits($request);

        $plan->update($data);
        return redirect()->route('admin.pricing.index')->with('success', 'Plan updated successfully.');
    }

    public function destroy($id)
    {
        $plan = PricingPlan::findOrFail($id);
        $plan->delete();
        return back()->with('success', 'Plan deleted.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Subscriptions & Payment Proofs
    // ─────────────────────────────────────────────────────────────────────────

    public function subscriptions(Request $request)
    {
        $status = $request->query('status', 'pending_approval');

        $query = Subscription::with(['user', 'plan', 'paymentProofs'])
            ->orderBy('created_at', 'desc');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $subscriptions = $query->paginate(20);

        return view('admin.pricing.subscriptions', compact('subscriptions', 'status'));
    }

    public function viewSubscription($id)
    {
        $subscription = Subscription::with(['user', 'plan', 'paymentProofs', 'toolLimits'])->findOrFail($id);
        return view('admin.pricing.subscription-detail', compact('subscription'));
    }

    public function approveSubscription(Request $request, $id)
    {
        $subscription = Subscription::findOrFail($id);

        if (!$subscription->isPending()) {
            return back()->with('error', 'This subscription has already been processed.');
        }

        $subscription->activate();

        // Mark the latest payment proof as approved
        $subscription->paymentProofs()->where('status', 'pending')->update([
            'status'      => 'approved',
            'admin_notes' => $request->admin_notes,
        ]);

        return redirect()->route('admin.pricing.subscriptions')
            ->with('success', 'Subscription approved and limits granted.');
    }

    public function rejectSubscription(Request $request, $id)
    {
        $request->validate(['admin_notes' => 'required|string|max:1000']);

        $subscription = Subscription::findOrFail($id);

        if (!$subscription->isPending()) {
            return back()->with('error', 'This subscription has already been processed.');
        }

        $subscription->status = Subscription::STATUS_REJECTED;
        $subscription->save();

        $subscription->paymentProofs()->where('status', 'pending')->update([
            'status'      => 'rejected',
            'admin_notes' => $request->admin_notes,
        ]);

        return redirect()->route('admin.pricing.subscriptions')
            ->with('success', 'Subscription rejected.');
    }

    public function resetUserLimits($subscriptionId)
    {
        $subscription = Subscription::findOrFail($subscriptionId);
        $subscription->toolLimits()->update(['used_count' => 0]);

        return back()->with('success', 'Usage limits reset for this subscriber.');
    }

    public function updateUserLimit(Request $request, $limitId)
    {
        $request->validate([
            'limit_count' => 'required|integer|min:-1',
            'bonus_count' => 'required|integer|min:0',
        ]);

        $limit = UserToolLimit::findOrFail($limitId);
        $limit->update([
            'limit_count' => $request->limit_count,
            'bonus_count' => $request->bonus_count,
        ]);

        return back()->with('success', 'Limit updated.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Private helpers
    // ─────────────────────────────────────────────────────────────────────────

    private function validatePlan(Request $request): array
    {
        return $request->validate([
            'name'           => 'required|string|max:100',
            'description'    => 'nullable|string|max:500',
            'price'          => 'required|numeric|min:0',
            'billing_cycle'  => 'required|in:monthly,yearly',
            'trial_days'     => 'nullable|integer|min:0',
            'is_active'      => 'nullable|boolean',
            'is_featured'    => 'nullable|boolean',
            'sort_order'     => 'required|integer|min:0',
            'promo_price'    => 'nullable|numeric|min:0',
            'promo_start_at' => 'nullable|date',
            'promo_end_at'   => 'nullable|date|after:promo_start_at',
        ]);
    }

    /**
     * Build the JSON limits array from individual form inputs.
     * A value of "" or "unlimited" is stored as -1.
     */
    private function buildLimits(Request $request): array
    {
        $tools = [
            'market_analysis', 'keyword_magnet', 'reverse_asin',
            'fba_calculator', 'cerebro', 'analyze_product', 'search_volume',
        ];
        $limits = [];
        foreach ($tools as $tool) {
            $val = $request->input("limit_{$tool}");
            $limits[$tool] = ($val === '' || $val === 'unlimited' || $val === null) ? -1 : (int) $val;
        }
        return $limits;
    }
}

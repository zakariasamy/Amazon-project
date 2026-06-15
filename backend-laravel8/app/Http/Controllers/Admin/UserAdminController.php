<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\PricingPlan;
use App\Models\Subscription;
use App\Models\UserToolLimit;
use Illuminate\Http\Request;
use Carbon\Carbon;

class UserAdminController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        
        $users = User::query()
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
            })
            ->orderBy('id', 'desc')
            ->paginate(20)
            ->withQueryString();

        $plans = PricingPlan::orderBy('price', 'asc')->get();

        return view('admin.users', compact('users', 'plans', 'search'));
    }

    public function create()
    {
        $plans = PricingPlan::orderBy('price', 'asc')->get();
        return view('admin.users-create', compact('plans'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'            => 'required|string|max:255',
            'email'           => 'required|email|max:255|unique:users,email',
            'password'        => 'required|string|min:8',
            'role'            => 'required|integer|in:0,1',
            'pricing_plan_id' => 'nullable|exists:pricing_plans,id',
            'duration_days'   => 'nullable|integer|min:1|max:365',
        ]);

        // Create the user
        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => bcrypt($data['password']),
            'role'     => (int) $data['role'],
        ]);

        // If pricing_plan_id is selected, give trial
        if (!empty($data['pricing_plan_id'])) {
            $plan = PricingPlan::findOrFail($data['pricing_plan_id']);
            $days = (int) (($data['duration_days'] ?? null) ?: ($plan->trial_days ?: 30));

            $sub = new Subscription();
            $sub->user_id = $user->id;
            $sub->pricing_plan_id = $plan->id;
            $sub->status = Subscription::STATUS_ACTIVE;
            $sub->current_period_start = Carbon::now();
            $sub->current_period_end = Carbon::now()->addDays($days);
            $sub->save();

            // Initialize user tool limits for the trial period
            $limits = $plan->limits ?? [];
            foreach ($limits as $tool => $count) {
                UserToolLimit::updateOrCreate(
                    [
                        'subscription_id' => $sub->id,
                        'tool_name'       => $tool,
                    ],
                    [
                        'user_id'       => $user->id,
                        'limit_count'   => $count,
                        'bonus_count'   => 0,
                        'used_count'    => 0,
                        'next_reset_at' => $sub->current_period_end,
                    ]
                );
            }
        }

        return redirect()->route('admin.users.index')->with('success', "User '{$user->email}' created successfully" . (!empty($data['pricing_plan_id']) ? " with free trial." : "."));
    }

    public function giveTrial(Request $request, $id)
    {
        $request->validate([
            'pricing_plan_id' => 'required|exists:pricing_plans,id',
            'duration_days'   => 'nullable|integer|min:1|max:365',
        ]);

        $user = User::findOrFail($id);
        $plan = PricingPlan::findOrFail($request->input('pricing_plan_id'));
        $daysInput = $request->input('duration_days');
        $days = (int) ($daysInput ?: ($plan->trial_days ?: 30));

        // Deactivate existing active subscriptions
        Subscription::where('user_id', $user->id)
            ->where('status', Subscription::STATUS_ACTIVE)
            ->update(['status' => Subscription::STATUS_EXPIRED]);

        // Create new active subscription representing the trial
        $sub = new Subscription();
        $sub->user_id = $user->id;
        $sub->pricing_plan_id = $plan->id;
        $sub->status = Subscription::STATUS_ACTIVE;
        $sub->current_period_start = Carbon::now();
        $sub->current_period_end = Carbon::now()->addDays($days);
        $sub->save();

        // Initialize user tool limits for the trial period
        $limits = $plan->limits ?? [];
        foreach ($limits as $tool => $count) {
            UserToolLimit::updateOrCreate(
                [
                    'subscription_id' => $sub->id,
                    'tool_name'       => $tool,
                ],
                [
                    'user_id'       => $user->id,
                    'limit_count'   => $count,
                    'bonus_count'   => 0,
                    'used_count'    => 0,
                    'next_reset_at' => $sub->current_period_end, // Expiry reset matches trial duration
                ]
            );
        }

        return redirect()->back()->with('success', "Assigned {$days}-day free trial of the '{$plan->name}' plan to user '{$user->email}'.");
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        return view('admin.users-edit', compact('user'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8',
            'role'     => 'required|integer|in:0,1',
        ]);

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->role = (int) $data['role'];

        if (!empty($data['password'])) {
            $user->password = bcrypt($data['password']);
        }

        $user->save();

        return redirect()->route('admin.users.index')->with('success', "User '{$user->email}' updated successfully.");
    }
}

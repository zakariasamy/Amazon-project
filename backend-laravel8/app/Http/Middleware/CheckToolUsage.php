<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\UserToolLimit;
use App\Models\Subscription;

class CheckToolUsage
{
    /**
     * Handle an incoming request.
     *
     * Applied as: Route::middleware('check.tool:market_analysis')
     *
     * Flow:
     *  1. Confirm user is authenticated (sanctum already checked, but defensive)
     *  2. Find the active subscription. If none, check the free plan limits.
     *  3. Run maybeLazyReset() to handle expired periods.
     *  4. Reject with 429 if the limit is exhausted.
     *  5. Let the request proceed ($next).
     *  6. If the response is 2xx, increment used_count by 1.
     *
     * @param  string  $tool  The tool_name key (e.g., market_analysis)
     */
    public function handle(Request $request, Closure $next, string $tool)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        // Admins bypass limit checks entirely (no limits calculated/enforced/incremented for them)
        if ($user->isAdmin()) {
            return $next($request);
        }

        // ── 1. Find the active subscription (or fall back to free plan row) ──
        $sub = $user->activeSubscription();

        // If no active paid subscription, check if there's a Free plan in DB
        if (!$sub) {
            $freePlan = \App\Models\PricingPlan::where('slug', 'free')
                ->where('is_active', true)
                ->first();

            if ($freePlan) {
                // Create or find a pseudo-free subscription for limit tracking
                $sub = Subscription::firstOrCreate(
                    ['user_id' => $user->id, 'pricing_plan_id' => $freePlan->id, 'status' => Subscription::STATUS_ACTIVE],
                    [
                        'current_period_start' => now()->startOfMonth(),
                        'current_period_end'   => now()->endOfMonth(),
                    ]
                );
                // Activate it if it doesn't have limits yet
                if ($sub->toolLimits()->count() === 0) {
                    $sub->activate();
                }
            } else {
                // No plans seeded at all — allow freely (dev mode)
                return $next($request);
            }
        }

        // ── 2. Get/create the limit row for this tool ─────────────────────────
        $limit = UserToolLimit::firstOrCreate(
            ['subscription_id' => $sub->id, 'tool_name' => $tool],
            [
                'user_id'       => $user->id,
                'limit_count'   => $sub->plan->getLimitFor($tool),
                'bonus_count'   => 0,
                'used_count'    => 0,
                'next_reset_at' => now()->addMonth(),
            ]
        );

        // ── 3. Lazy monthly reset ─────────────────────────────────────────────
        $limit->maybeLazyReset();

        // ── 4. Enforce limit ──────────────────────────────────────────────────
        if (!$limit->hasRemaining()) {
            $cap = $limit->isUnlimited() ? 'unlimited' : ($limit->limit_count + $limit->bonus_count);
            return response()->json([
                'error'     => "You have reached your monthly limit for this tool. Please upgrade your plan.",
                'tool'      => $tool,
                'limit'     => $cap,
                'used'      => $limit->used_count,
                'plan'      => $sub->plan->name,
                'reset_at'  => $limit->next_reset_at,
                'upgrade_url' => url('/#pricing'),
            ], 429);
        }

        // ── 5. Attach limit to request (controllers can use it optionally) ────
        $request->attributes->set('tool_limit', $limit);

        // ── 6. Execute the tool handler ───────────────────────────────────────
        $response = $next($request);

        // ── 7. Auto-consume 1 usage only on success (2xx) ─────────────────────
        $status = $response->getStatusCode();
        if ($status >= 200 && $status < 300) {
            $limit->consume(); // increments used_count by 1 and saves
        }

        return $response;
    }
}


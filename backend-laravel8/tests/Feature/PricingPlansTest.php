<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\PricingPlan;
use App\Models\Subscription;
use App\Models\UserToolLimit;
use App\Models\PaymentProof;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PricingPlansTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Clear pricing plans since the seed migration automatically populates them,
        // which would cause integrity violations on our custom plan creations.
        \DB::table('pricing_plans')->delete();
    }

    /**
     * Test public pricing plans list filters and pricing structure.
     */
    public function test_public_pricing_plans_list_active_and_promo_pricing()
    {
        // 1. Create an active plan
        $activePlan = PricingPlan::create([
            'name' => 'Pro Monthly',
            'slug' => 'pro-monthly',
            'price' => 49.99,
            'billing_cycle' => 'monthly',
            'limits' => ['market_analysis' => 100],
            'is_active' => true,
            'sort_order' => 1,
        ]);

        // 2. Create an inactive plan
        $inactivePlan = PricingPlan::create([
            'name' => 'Legacy Plan',
            'slug' => 'legacy-plan',
            'price' => 19.99,
            'billing_cycle' => 'monthly',
            'limits' => ['market_analysis' => 20],
            'is_active' => false,
            'sort_order' => 2,
        ]);

        // 3. Create a plan with an active promotion
        $promoPlan = PricingPlan::create([
            'name' => 'Promo Plan',
            'slug' => 'promo-plan',
            'price' => 99.99,
            'promo_price' => 79.99,
            'promo_start_at' => now()->subDay(),
            'promo_end_at' => now()->addDay(),
            'billing_cycle' => 'monthly',
            'limits' => ['market_analysis' => 200],
            'is_active' => true,
            'sort_order' => 3,
        ]);

        $response = $this->getJson('/api/pricing-plans');

        $response->assertStatus(200);
        $response->assertJsonCount(2);

        // Assert inactive plan is not returned
        $response->assertJsonMissing(['slug' => 'legacy-plan']);

        // Assert promo plan returns correct effective_price and is_promo = true
        $response->assertJsonFragment([
            'slug' => 'promo-plan',
            'price' => '99.99',
            'effective_price' => 79.99,
            'is_promo' => true,
        ]);
    }

    /**
     * Test check.tool middleware enforces limit and increments on success.
     */
    public function test_check_tool_middleware_enforces_limits_and_increments()
    {
        $user = User::factory()->create();

        $plan = PricingPlan::create([
            'name' => 'Basic',
            'slug' => 'basic',
            'price' => 29.99,
            'billing_cycle' => 'monthly',
            'limits' => ['market_analysis' => 2],
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $sub = Subscription::create([
            'user_id' => $user->id,
            'pricing_plan_id' => $plan->id,
            'status' => Subscription::STATUS_ACTIVE,
            'current_period_start' => now(),
            'current_period_end' => now()->addMonth(),
        ]);

        // Instantiation of limits
        $limit = UserToolLimit::create([
            'subscription_id' => $sub->id,
            'user_id' => $user->id,
            'tool_name' => 'market_analysis',
            'limit_count' => 2,
            'bonus_count' => 0,
            'used_count' => 0,
            'next_reset_at' => now()->addMonth(),
        ]);

        // Mock a route that calls ProductAnalysisController@analyze
        // Passing BSR > 0 to ensure the source is set to 'bsr_estimate' (valid enum value in DB)
        // Usage 1
        $response1 = $this->actingAs($user, 'sanctum')->postJson('/api/analyze', [
            'asin' => 'B08P5MP4YC',
            'marketplace' => 'amazon.eg',
            'bsr' => 1000
        ]);
        $response1->assertStatus(200);
        $this->assertEquals(1, $limit->refresh()->used_count);

        // Usage 2
        $response2 = $this->actingAs($user, 'sanctum')->postJson('/api/analyze', [
            'asin' => 'B08P5MP4YC',
            'marketplace' => 'amazon.eg',
            'bsr' => 1000
        ]);
        $response2->assertStatus(200);
        $this->assertEquals(2, $limit->refresh()->used_count);

        // Usage 3 (exhausted)
        $response3 = $this->actingAs($user, 'sanctum')->postJson('/api/analyze', [
            'asin' => 'B08P5MP4YC',
            'marketplace' => 'amazon.eg',
            'bsr' => 1000
        ]);
        $response3->assertStatus(429);
        $response3->assertJsonStructure(['error', 'upgrade_url']);
        $this->assertEquals(2, $limit->refresh()->used_count); // remains 2
    }

    /**
     * Test unlimited limits bypass limit checking.
     */
    public function test_unlimited_tools_bypass_count_checks()
    {
        $user = User::factory()->create();

        $plan = PricingPlan::create([
            'name' => 'Pro Unlimited',
            'slug' => 'pro-unlimited',
            'price' => 99.99,
            'billing_cycle' => 'monthly',
            'limits' => ['cerebro' => -1],
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $sub = Subscription::create([
            'user_id' => $user->id,
            'pricing_plan_id' => $plan->id,
            'status' => Subscription::STATUS_ACTIVE,
            'current_period_start' => now(),
            'current_period_end' => now()->addMonth(),
        ]);

        $limit = UserToolLimit::create([
            'subscription_id' => $sub->id,
            'user_id' => $user->id,
            'tool_name' => 'cerebro',
            'limit_count' => -1, // Unlimited
            'bonus_count' => 0,
            'used_count' => 100, // already used a lot
            'next_reset_at' => now()->addMonth(),
        ]);

        // Request should still succeed - passing required parameters for Cerebro
        $response = $this->actingAs($user, 'sanctum')->postJson('/api/cerebro/analyze', [
            'asins' => ['B08P5MP4YC'],
            'marketplace' => 'amazon.eg',
            'keywords' => [
                ['keyword' => 'body scale', 'search_volume' => 100]
            ]
        ]);

        $response->assertStatus(200);
        $this->assertEquals(101, $limit->refresh()->used_count);
    }

    /**
     * Test lazy monthly resets logic.
     */
    public function test_lazy_monthly_resets_activate_correctly_on_expired_periods()
    {
        $user = User::factory()->create();

        $plan = PricingPlan::create([
            'name' => 'Basic',
            'slug' => 'basic',
            'price' => 29.99,
            'billing_cycle' => 'monthly',
            'limits' => ['market_analysis' => 5],
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $sub = Subscription::create([
            'user_id' => $user->id,
            'pricing_plan_id' => $plan->id,
            'status' => Subscription::STATUS_ACTIVE,
            'current_period_start' => now()->subMonths(2),
            'current_period_end' => now()->addMonth(),
        ]);

        // Limit created 2 months ago, expired next_reset_at 1 month ago
        $limit = UserToolLimit::create([
            'subscription_id' => $sub->id,
            'user_id' => $user->id,
            'tool_name' => 'market_analysis',
            'limit_count' => 5,
            'bonus_count' => 0,
            'used_count' => 5, // fully used up in the past
            'next_reset_at' => now()->subDay(), // expired
        ]);

        // A new request should trigger lazy reset, clearing used_count to 0, then incrementing to 1
        $response = $this->actingAs($user, 'sanctum')->postJson('/api/analyze', [
            'asin' => 'B08P5MP4YC',
            'marketplace' => 'amazon.eg',
            'bsr' => 1000
        ]);

        $response->assertStatus(200);
        $limit->refresh();
        $this->assertEquals(1, $limit->used_count);
        $this->assertTrue($limit->next_reset_at->gt(now()));
    }

    /**
     * Test plan upgrades carry over unused limits.
     */
    public function test_upgrade_plan_carries_over_remaining_limits()
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $basicPlan = PricingPlan::create([
            'name' => 'Basic',
            'slug' => 'basic',
            'price' => 29.99,
            'billing_cycle' => 'monthly',
            'limits' => ['market_analysis' => 10],
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $proPlan = PricingPlan::create([
            'name' => 'Pro',
            'slug' => 'pro',
            'price' => 59.99,
            'billing_cycle' => 'monthly',
            'limits' => ['market_analysis' => 50],
            'is_active' => true,
            'sort_order' => 2,
        ]);

        $oldSub = Subscription::create([
            'user_id' => $user->id,
            'pricing_plan_id' => $basicPlan->id,
            'status' => Subscription::STATUS_ACTIVE,
            'current_period_start' => now()->subDays(10),
            'current_period_end' => now()->addDays(20),
        ]);

        UserToolLimit::create([
            'subscription_id' => $oldSub->id,
            'user_id' => $user->id,
            'tool_name' => 'market_analysis',
            'limit_count' => 10,
            'bonus_count' => 0,
            'used_count' => 3, // 7 remaining
            'next_reset_at' => now()->addDays(20),
        ]);

        // Submit upgrade proof
        $file = UploadedFile::fake()->create('proof.jpg', 100, 'image/jpeg');
        $response = $this->actingAs($user, 'sanctum')->postJson('/api/subscription/upgrade', [
            'plan_id' => $proPlan->id,
            'proof_image' => $file,
        ]);

        $response->assertStatus(201);
        $newSubId = $response->json('subscription_id');

        $newSub = Subscription::findOrFail($newSubId);
        $this->assertEquals(Subscription::STATUS_PENDING_APPROVAL, $newSub->status);

        // Approve and activate via controller logic
        $newSub->activate();

        $newSub->refresh();
        $oldSub->refresh();

        // Old sub should be marked expired
        $this->assertEquals(Subscription::STATUS_EXPIRED, $oldSub->status);
        // New sub should be active
        $this->assertEquals(Subscription::STATUS_ACTIVE, $newSub->status);

        // New limit should be: 50 (Pro limit) + 7 (remaining Basic limit) = 57
        $newLimit = UserToolLimit::where('subscription_id', $newSub->id)
            ->where('tool_name', 'market_analysis')
            ->first();

        $this->assertNotNull($newLimit);
        $this->assertEquals(57, $newLimit->limit_count);
        $this->assertEquals(0, $newLimit->used_count);
    }

    /**
     * Test that non-admin users cannot access admin settings.
     */
    public function test_non_admin_cannot_access_admin_settings()
    {
        $user = User::factory()->create(['role' => User::ROLE_USER]);

        $response = $this->actingAs($user)->get('/admin/settings');

        $response->assertStatus(403);
    }

    /**
     * Test that admin users can access admin settings.
     */
    public function test_admin_can_access_admin_settings()
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $response = $this->actingAs($admin)->get('/admin/settings');

        $response->assertStatus(200);
    }

    /**
     * Test that admin users bypass tool limit checks completely.
     */
    public function test_admin_bypasses_tool_limits_entirely()
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        // Mock a route that calls ProductAnalysisController@analyze
        // Admin has no subscription, but should bypass middleware and execute request successfully
        $response = $this->actingAs($admin, 'sanctum')->postJson('/api/analyze', [
            'asin' => 'B08P5MP4YC',
            'marketplace' => 'amazon.eg',
            'bsr' => 1000
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment(['asin' => 'B08P5MP4YC']);
    }

    /**
     * Test that an expired active subscription is automatically transitioned to 'expired' and returns null.
     */
    public function test_expired_subscription_is_lazy_expired_and_returns_null()
    {
        $user = User::factory()->create();
        $plan = PricingPlan::create([
            'name' => 'Elite Plan',
            'slug' => 'elite-plan',
            'price' => 149.99,
            'billing_cycle' => 'monthly',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $sub = Subscription::create([
            'user_id' => $user->id,
            'pricing_plan_id' => $plan->id,
            'status' => Subscription::STATUS_ACTIVE,
            'current_period_start' => Carbon::now()->subDays(31),
            'current_period_end' => Carbon::now()->subMinutes(5), // expired 5 minutes ago
        ]);

        $activeSub = $user->activeSubscription();
        $this->assertNull($activeSub);

        $sub->refresh();
        $this->assertEquals(Subscription::STATUS_EXPIRED, $sub->status);
    }
}

<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\PricingPlan;
use App\Models\Subscription;
use App\Models\PaymentProof;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SubscriptionWebTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Truncate tables to prevent unique constraint/primary key integrity conflicts with seeder data
        \DB::table('pricing_plans')->delete();
        \DB::table('users')->delete();
        Storage::fake('public');
    }

    public function test_guest_redirected_to_login()
    {
        $response = $this->get('/subscription/upgrade');
        $response->assertRedirect('/login');

        $responsePay = $this->get('/subscription/pay/1');
        $responsePay->assertRedirect('/login');
    }

    public function test_user_can_view_upgrade_page()
    {
        $user = User::create([
            'name'     => 'Regular User',
            'email'    => 'user@example.com',
            'password' => bcrypt('password'),
            'role'     => User::ROLE_USER,
        ]);

        $plan = PricingPlan::create([
            'name'          => 'Gold Plan',
            'slug'          => 'gold-plan',
            'price'         => 49.00,
            'billing_cycle' => 'monthly',
            'trial_days'    => 0,
            'limits'        => ['cerebro' => 100],
            'is_active'     => true,
            'sort_order'    => 1,
        ]);

        $response = $this->actingAs($user)->get('/subscription/upgrade');
        $response->assertStatus(200);
        $response->assertSee('Gold Plan');
    }

    public function test_user_can_submit_payment_proof()
    {
        $user = User::create([
            'name'     => 'Regular User',
            'email'    => 'user@example.com',
            'password' => bcrypt('password'),
            'role'     => User::ROLE_USER,
        ]);

        $plan = PricingPlan::create([
            'name'          => 'Gold Plan',
            'slug'          => 'gold-plan',
            'price'         => 49.00,
            'billing_cycle' => 'monthly',
            'trial_days'    => 0,
            'limits'        => ['cerebro' => 100],
            'is_active'     => true,
            'sort_order'    => 1,
        ]);

        $file = UploadedFile::fake()->create('proof.jpg', 100, 'image/jpeg');

        $response = $this->actingAs($user)->post('/subscription/pay', [
            'plan_id'     => $plan->id,
            'proof_image' => $file,
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertDatabaseHas('subscriptions', [
            'user_id'         => $user->id,
            'pricing_plan_id' => $plan->id,
            'status'          => Subscription::STATUS_PENDING_APPROVAL,
        ]);

        $sub = Subscription::where('user_id', $user->id)->first();
        $this->assertNotNull($sub);

        $this->assertDatabaseHas('payment_proofs', [
            'subscription_id' => $sub->id,
            'status'          => PaymentProof::STATUS_PENDING,
        ]);
    }

    public function test_user_upgrade_calculates_carry_over()
    {
        $user = User::create([
            'name'     => 'Regular User',
            'email'    => 'user@example.com',
            'password' => bcrypt('password'),
            'role'     => User::ROLE_USER,
        ]);

        $planOld = PricingPlan::create([
            'name'          => 'Old Plan',
            'slug'          => 'old-plan',
            'price'         => 19.00,
            'billing_cycle' => 'monthly',
            'trial_days'    => 0,
            'limits'        => ['cerebro' => 50],
            'is_active'     => true,
            'sort_order'    => 1,
        ]);

        $planNew = PricingPlan::create([
            'name'          => 'New Plan',
            'slug'          => 'new-plan',
            'price'         => 49.00,
            'billing_cycle' => 'monthly',
            'trial_days'    => 0,
            'limits'        => ['cerebro' => 100],
            'is_active'     => true,
            'sort_order'    => 2,
        ]);

        // Active subscription on old plan
        $sub = Subscription::create([
            'user_id'              => $user->id,
            'pricing_plan_id'      => $planOld->id,
            'status'               => Subscription::STATUS_ACTIVE,
            'current_period_start' => now(),
            'current_period_end'   => now()->addMonth(),
        ]);

        // Tool limits: limit_count = 50, used_count = 10 (40 remaining)
        $sub->toolLimits()->create([
            'user_id'       => $user->id,
            'tool_name'     => 'cerebro',
            'limit_count'   => 50,
            'bonus_count'   => 0,
            'used_count'    => 10,
            'next_reset_at' => now()->addMonth(),
        ]);

        $file = UploadedFile::fake()->create('proof.jpg', 100, 'image/jpeg');

        $response = $this->actingAs($user)->post('/subscription/pay', [
            'plan_id'     => $planNew->id,
            'proof_image' => $file,
        ]);

        $response->assertRedirect('/dashboard');

        // Verify carry-over note exists in payment proof
        $newSub = Subscription::where('pricing_plan_id', $planNew->id)->first();
        $this->assertNotNull($newSub);

        $proof = PaymentProof::where('subscription_id', $newSub->id)->first();
        $this->assertNotNull($proof);
        $this->assertStringContainsString('UPGRADE from plan #' . $planOld->id, $proof->admin_notes);
        $this->assertStringContainsString('"old_remaining":40', $proof->admin_notes);
        $this->assertStringContainsString('"new_limit":140', $proof->admin_notes);
    }
}

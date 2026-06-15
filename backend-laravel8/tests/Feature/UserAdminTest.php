<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\PricingPlan;
use App\Models\Subscription;
use App\Models\UserToolLimit;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Truncate tables to prevent unique constraint/primary key integrity conflicts with seeder data
        \DB::table('pricing_plans')->delete();
        \DB::table('users')->delete();
    }

    public function test_non_admin_cannot_access_user_management()
    {
        $user = User::create([
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_USER,
        ]);

        $response = $this->actingAs($user)->get('/admin/users');
        $response->assertStatus(403);
    }

    public function test_admin_can_access_user_management_and_search()
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_ADMIN,
        ]);

        $user1 = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_USER,
        ]);

        $user2 = User::create([
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_USER,
        ]);

        // Access dashboard users list
        $response = $this->actingAs($admin)->get('/admin/users');
        $response->assertStatus(200);
        $response->assertSee('John Doe');
        $response->assertSee('Jane Smith');

        // Search for John
        $responseSearch = $this->actingAs($admin)->get('/admin/users?search=John');
        $responseSearch->assertStatus(200);
        $responseSearch->assertSee('John Doe');
        $responseSearch->assertDontSee('Jane Smith');

        // Search for jane email
        $responseSearchEmail = $this->actingAs($admin)->get('/admin/users?search=jane@example.com');
        $responseSearchEmail->assertStatus(200);
        $responseSearchEmail->assertSee('Jane Smith');
        $responseSearchEmail->assertDontSee('John Doe');
    }

    public function test_admin_can_assign_free_trial_to_user()
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_ADMIN,
        ]);

        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_USER,
        ]);

        $plan = PricingPlan::create([
            'name' => 'Elite Plan',
            'slug' => 'elite-plan',
            'price' => 149.99,
            'billing_cycle' => 'monthly',
            'limits' => [
                'market_analysis' => 500,
                'keyword_magnet' => 200,
            ],
            'is_active' => true,
            'sort_order' => 1,
        ]);

        // Pre-create an active subscription to verify it gets deactivated (status => expired)
        $oldPlan = PricingPlan::create([
            'name' => 'Starter Plan',
            'slug' => 'starter-plan',
            'price' => 29.99,
            'billing_cycle' => 'monthly',
            'limits' => [
                'market_analysis' => 50,
            ],
            'is_active' => true,
            'sort_order' => 2,
        ]);

        $oldSub = Subscription::create([
            'user_id' => $user->id,
            'pricing_plan_id' => $oldPlan->id,
            'status' => Subscription::STATUS_ACTIVE,
            'current_period_start' => Carbon::now()->subDays(5),
            'current_period_end' => Carbon::now()->addDays(25),
        ]);

        // Post request to assign trial
        $response = $this->actingAs($admin)->post("/admin/users/{$user->id}/trial", [
            'pricing_plan_id' => $plan->id,
            'duration_days' => 15,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Check old subscription is now expired
        $oldSub->refresh();
        $this->assertEquals(Subscription::STATUS_EXPIRED, $oldSub->status);

        // Check new subscription is active and has correct periods
        $newSub = Subscription::where('user_id', $user->id)
            ->where('status', Subscription::STATUS_ACTIVE)
            ->first();

        $this->assertNotNull($newSub);
        $this->assertEquals($plan->id, $newSub->pricing_plan_id);
        $this->assertTrue(Carbon::now()->diffInSeconds($newSub->current_period_start) < 5);
        $this->assertTrue(Carbon::now()->addDays(15)->diffInSeconds($newSub->current_period_end) < 5);

        // Check limits were created/updated correctly
        $limits = UserToolLimit::where('subscription_id', $newSub->id)->get()->keyBy('tool_name');
        $this->assertCount(2, $limits);

        $this->assertArrayHasKey('market_analysis', $limits);
        $this->assertEquals(500, $limits['market_analysis']->limit_count);
        $this->assertEquals(0, $limits['market_analysis']->used_count);
        $this->assertTrue($newSub->current_period_end->equalTo($limits['market_analysis']->next_reset_at));

        $this->assertArrayHasKey('keyword_magnet', $limits);
        $this->assertEquals(200, $limits['keyword_magnet']->limit_count);
    }

    public function test_non_admin_cannot_create_user()
    {
        $user = User::create([
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_USER,
        ]);

        $responseGet = $this->actingAs($user)->get('/admin/users/create');
        $responseGet->assertStatus(403);

        $responsePost = $this->actingAs($user)->post('/admin/users', [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'newpassword123',
            'role' => 0,
        ]);
        $responsePost->assertStatus(403);
    }

    public function test_admin_can_create_user_without_trial()
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_ADMIN,
        ]);

        $responseGet = $this->actingAs($admin)->get('/admin/users/create');
        $responseGet->assertStatus(200);
        $responseGet->assertSee('Create User');

        $responsePost = $this->actingAs($admin)->post('/admin/users', [
            'name' => 'Jane Cooper',
            'email' => 'jane.cooper@example.com',
            'password' => 'secret_password_999',
            'role' => 0,
        ]);

        $responsePost->assertRedirect(route('admin.users.index'));
        $responsePost->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'email' => 'jane.cooper@example.com',
            'name' => 'Jane Cooper',
            'role' => User::ROLE_USER,
        ]);

        $newUser = User::where('email', 'jane.cooper@example.com')->first();
        $this->assertNotNull($newUser);
        $this->assertTrue(\Hash::check('secret_password_999', $newUser->password));
        $this->assertNull($newUser->activeSubscription());
    }

    public function test_admin_can_create_user_with_initial_trial()
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_ADMIN,
        ]);

        $plan = PricingPlan::create([
            'name' => 'Elite Plan',
            'slug' => 'elite-plan',
            'price' => 149.99,
            'billing_cycle' => 'monthly',
            'limits' => [
                'market_analysis' => 500,
            ],
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $responsePost = $this->actingAs($admin)->post('/admin/users', [
            'name' => 'Trial User',
            'email' => 'trial.user@example.com',
            'password' => 'password123',
            'role' => 0,
            'pricing_plan_id' => $plan->id,
            'duration_days' => 14,
        ]);

        $responsePost->assertRedirect(route('admin.users.index'));
        $responsePost->assertSessionHas('success');

        $newUser = User::where('email', 'trial.user@example.com')->first();
        $this->assertNotNull($newUser);

        $sub = $newUser->activeSubscription();
        $this->assertNotNull($sub);
        $this->assertEquals($plan->id, $sub->pricing_plan_id);
        $this->assertEquals(Subscription::STATUS_ACTIVE, $sub->status);
        $this->assertTrue(Carbon::now()->addDays(14)->diffInSeconds($sub->current_period_end) < 5);

        $limits = UserToolLimit::where('subscription_id', $sub->id)->get()->keyBy('tool_name');
        $this->assertCount(1, $limits);
        $this->assertEquals(500, $limits['market_analysis']->limit_count);
    }

    public function test_admin_can_create_user_with_plan_default_trial_days()
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_ADMIN,
        ]);

        $plan = PricingPlan::create([
            'name' => 'Elite Plan',
            'slug' => 'elite-plan',
            'price' => 149.99,
            'billing_cycle' => 'monthly',
            'trial_days' => 7,
            'limits' => [
                'market_analysis' => 500,
            ],
            'is_active' => true,
            'sort_order' => 1,
        ]);

        // Post without duration_days to test plan default fallback
        $responsePost = $this->actingAs($admin)->post('/admin/users', [
            'name' => 'Trial User 2',
            'email' => 'trial.user2@example.com',
            'password' => 'password123',
            'role' => 0,
            'pricing_plan_id' => $plan->id,
            // duration_days is omitted
        ]);

        $responsePost->assertRedirect(route('admin.users.index'));
        $newUser = User::where('email', 'trial.user2@example.com')->first();
        $this->assertNotNull($newUser);

        $sub = $newUser->activeSubscription();
        $this->assertNotNull($sub);
        // Expiry should be 7 days from now (matching plan's trial_days)
        $this->assertTrue(Carbon::now()->addDays(7)->diffInSeconds($sub->current_period_end) < 5);
    }

    public function test_admin_can_give_trial_to_existing_user_with_plan_default_trial_days()
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_ADMIN,
        ]);

        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_USER,
        ]);

        $plan = PricingPlan::create([
            'name' => 'Elite Plan',
            'slug' => 'elite-plan',
            'price' => 149.99,
            'billing_cycle' => 'monthly',
            'trial_days' => 12,
            'limits' => [
                'market_analysis' => 500,
            ],
            'is_active' => true,
            'sort_order' => 1,
        ]);

        // Post request to assign trial without duration_days
        $response = $this->actingAs($admin)->post("/admin/users/{$user->id}/trial", [
            'pricing_plan_id' => $plan->id,
            // duration_days is omitted
        ]);

        $response->assertRedirect();
        
        $sub = $user->activeSubscription();
        $this->assertNotNull($sub);
        // Expiry should be 12 days from now (matching plan's trial_days)
        $this->assertTrue(Carbon::now()->addDays(12)->diffInSeconds($sub->current_period_end) < 5);
    }

    public function test_admin_can_edit_user()
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_ADMIN,
        ]);

        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_USER,
        ]);

        // 1. Visit edit page
        $response = $this->actingAs($admin)->get("/admin/users/{$user->id}/edit");
        $response->assertStatus(200);
        $response->assertSee('John Doe');

        // 2. Submit changes (update name, role, and keeping same email)
        $responsePut = $this->actingAs($admin)->put("/admin/users/{$user->id}", [
            'name'  => 'John Updated',
            'email' => 'john@example.com',
            'role'  => User::ROLE_ADMIN, // make admin
        ]);

        $responsePut->assertRedirect(route('admin.users.index'));
        
        $user->refresh();
        $this->assertEquals('John Updated', $user->name);
        $this->assertTrue($user->isAdmin());

        // 3. Update password
        $oldPassHash = $user->password;
        $responsePass = $this->actingAs($admin)->put("/admin/users/{$user->id}", [
            'name'     => 'John Updated',
            'email'    => 'john@example.com',
            'role'     => User::ROLE_ADMIN,
            'password' => 'newpassword123',
        ]);
        
        $user->refresh();
        $this->assertNotEquals($oldPassHash, $user->password);
    }

    public function test_non_admin_cannot_edit_user()
    {
        $user = User::create([
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_USER,
        ]);

        $targetUser = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_USER,
        ]);

        $responseGet = $this->actingAs($user)->get("/admin/users/{$targetUser->id}/edit");
        $responseGet->assertStatus(403);

        $responsePut = $this->actingAs($user)->put("/admin/users/{$targetUser->id}", [
            'name'  => 'Hack Name',
            'email' => 'hack@example.com',
            'role'  => User::ROLE_ADMIN,
        ]);
        $responsePut->assertStatus(403);
    }
}

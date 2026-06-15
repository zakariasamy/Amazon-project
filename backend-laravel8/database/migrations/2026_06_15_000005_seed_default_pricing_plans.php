<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class SeedDefaultPricingPlans extends Migration
{
    public function up()
    {
        $now = now();

        DB::table('pricing_plans')->insert([
            [
                'name'         => 'Free',
                'slug'         => 'free',
                'description'  => 'Perfect for beginners exploring Amazon selling.',
                'price'        => 0.00,
                'billing_cycle'=> 'monthly',
                'limits'       => json_encode([
                    'market_analysis'    => 10,
                    'keyword_magnet'     => 10,
                    'reverse_asin'       => 5,
                    'fba_calculator'     => 20,
                    'cerebro'            => 5,
                    'analyze_product'    => 10,
                    'search_volume'      => 20,
                ]),
                'is_active'    => true,
                'is_featured'  => false,
                'sort_order'   => 1,
                'promo_price'  => null,
                'promo_start_at' => null,
                'promo_end_at' => null,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
            [
                'name'         => 'Pro',
                'slug'         => 'pro',
                'description'  => 'Everything you need to grow your Amazon business.',
                'price'        => 29.00,
                'billing_cycle'=> 'monthly',
                'limits'       => json_encode([
                    'market_analysis'    => -1,
                    'keyword_magnet'     => -1,
                    'reverse_asin'       => -1,
                    'fba_calculator'     => -1,
                    'cerebro'            => -1,
                    'analyze_product'    => -1,
                    'search_volume'      => -1,
                ]),
                'is_active'    => true,
                'is_featured'  => true,
                'sort_order'   => 2,
                'promo_price'  => null,
                'promo_start_at' => null,
                'promo_end_at' => null,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
            [
                'name'         => 'Enterprise',
                'slug'         => 'enterprise',
                'description'  => 'For power sellers and agencies managing multiple brands.',
                'price'        => 79.00,
                'billing_cycle'=> 'monthly',
                'limits'       => json_encode([
                    'market_analysis'    => -1,
                    'keyword_magnet'     => -1,
                    'reverse_asin'       => -1,
                    'fba_calculator'     => -1,
                    'cerebro'            => -1,
                    'analyze_product'    => -1,
                    'search_volume'      => -1,
                ]),
                'is_active'    => true,
                'is_featured'  => false,
                'sort_order'   => 3,
                'promo_price'  => null,
                'promo_start_at' => null,
                'promo_end_at' => null,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
        ]);
    }

    public function down()
    {
        DB::table('pricing_plans')->whereIn('slug', ['free', 'pro', 'enterprise'])->delete();
    }
}

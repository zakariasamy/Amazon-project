<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FbaFeesSeeder extends Seeder
{
    public function run()
    {
        $now = now();
        $effectiveDate = '2025-01-01';

        // Amazon US Referral Fees
        $usReferralFees = [
            ['category' => 'Electronics', 'referral_fee_percent' => 8.00, 'referral_fee_min' => 0.30],
            ['category' => 'Cell Phones & Accessories', 'referral_fee_percent' => 8.00, 'referral_fee_min' => 0.30],
            ['category' => 'Clothing, Shoes & Jewelry', 'referral_fee_percent' => 17.00, 'referral_fee_min' => 0.30],
            ['category' => 'Home & Kitchen', 'referral_fee_percent' => 15.00, 'referral_fee_min' => 0.30],
            ['category' => 'Beauty & Personal Care', 'referral_fee_percent' => 8.00, 'referral_fee_min' => 0.30],
            ['category' => 'Health & Household', 'referral_fee_percent' => 8.00, 'referral_fee_min' => 0.30],
            ['category' => 'Sports & Outdoors', 'referral_fee_percent' => 15.00, 'referral_fee_min' => 0.30],
            ['category' => 'Toys & Games', 'referral_fee_percent' => 15.00, 'referral_fee_min' => 0.30],
            ['category' => 'Grocery & Gourmet Food', 'referral_fee_percent' => 8.00, 'referral_fee_min' => 0.30],
            ['category' => 'Pet Supplies', 'referral_fee_percent' => 15.00, 'referral_fee_min' => 0.30],
            ['category' => 'Books', 'referral_fee_percent' => 15.00, 'referral_fee_min' => 0.00],
            ['category' => 'Office Products', 'referral_fee_percent' => 15.00, 'referral_fee_min' => 0.30],
            ['category' => 'default', 'referral_fee_percent' => 15.00, 'referral_fee_min' => 0.30],
        ];

        foreach ($usReferralFees as $fee) {
            DB::table('fba_fees')->insert([
                'marketplace' => 'amazon.com',
                'category' => $fee['category'],
                'referral_fee_percent' => $fee['referral_fee_percent'],
                'referral_fee_min' => $fee['referral_fee_min'],
                'effective_date' => $effectiveDate,
                'expiry_date' => null,
                'is_promotional' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // Amazon Egypt Referral Fees (typically higher due to smaller market)
        $egReferralFees = [
            ['category' => 'Electronics', 'referral_fee_percent' => 10.00, 'referral_fee_min' => 5.00],
            ['category' => 'Fashion', 'referral_fee_percent' => 15.00, 'referral_fee_min' => 5.00],
            ['category' => 'Home & Kitchen', 'referral_fee_percent' => 15.00, 'referral_fee_min' => 5.00],
            ['category' => 'Beauty', 'referral_fee_percent' => 10.00, 'referral_fee_min' => 5.00],
            ['category' => 'Sports', 'referral_fee_percent' => 15.00, 'referral_fee_min' => 5.00],
            ['category' => 'Baby Products', 'referral_fee_percent' => 15.00, 'referral_fee_min' => 5.00],
            ['category' => 'default', 'referral_fee_percent' => 15.00, 'referral_fee_min' => 5.00],
        ];

        foreach ($egReferralFees as $fee) {
            DB::table('fba_fees')->insert([
                'marketplace' => 'amazon.eg',
                'category' => $fee['category'],
                'referral_fee_percent' => $fee['referral_fee_percent'],
                'referral_fee_min' => $fee['referral_fee_min'],
                'effective_date' => $effectiveDate,
                'expiry_date' => null,
                'is_promotional' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // US Fulfillment Fees
        $usFulfillmentFees = [
            ['size_tier' => 'Small Standard', 'weight_max_kg' => 0.45, 'fee_low' => 3.22, 'fee_high' => 3.22, 'threshold' => 10],
            ['size_tier' => 'Large Standard (0-0.5 lb)', 'weight_max_kg' => 0.23, 'fee_low' => 3.86, 'fee_high' => 4.08, 'threshold' => 10],
            ['size_tier' => 'Large Standard (0.5-1 lb)', 'weight_max_kg' => 0.45, 'fee_low' => 4.08, 'fee_high' => 4.75, 'threshold' => 10],
            ['size_tier' => 'Large Standard (1-2 lb)', 'weight_max_kg' => 0.91, 'fee_low' => 5.13, 'fee_high' => 5.79, 'threshold' => 10],
            ['size_tier' => 'Large Standard (2-3 lb)', 'weight_max_kg' => 1.36, 'fee_low' => 5.46, 'fee_high' => 6.16, 'threshold' => 10],
            ['size_tier' => 'Small Oversize', 'weight_max_kg' => 31.75, 'fee_low' => 9.73, 'fee_high' => 9.73, 'threshold' => 0],
            ['size_tier' => 'Medium Oversize', 'weight_max_kg' => 68.04, 'fee_low' => 19.05, 'fee_high' => 19.05, 'threshold' => 0],
            ['size_tier' => 'Large Oversize', 'weight_max_kg' => 68.04, 'fee_low' => 89.98, 'fee_high' => 89.98, 'threshold' => 0],
        ];

        foreach ($usFulfillmentFees as $fee) {
            DB::table('fulfillment_fees')->insert([
                'marketplace' => 'amazon.com',
                'size_tier' => $fee['size_tier'],
                'weight_max_kg' => $fee['weight_max_kg'],
                'fee_low_price' => $fee['fee_low'],
                'fee_high_price' => $fee['fee_high'],
                'price_threshold' => $fee['threshold'],
                'effective_date' => $effectiveDate,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // Egypt Fulfillment Fees (in EGP)
        $egFulfillmentFees = [
            ['size_tier' => 'Small', 'weight_max_kg' => 0.5, 'fee_low' => 15.00, 'fee_high' => 15.00, 'threshold' => 0],
            ['size_tier' => 'Standard', 'weight_max_kg' => 2.0, 'fee_low' => 25.00, 'fee_high' => 30.00, 'threshold' => 0],
            ['size_tier' => 'Large', 'weight_max_kg' => 5.0, 'fee_low' => 45.00, 'fee_high' => 55.00, 'threshold' => 0],
            ['size_tier' => 'Oversize', 'weight_max_kg' => 30.0, 'fee_low' => 100.00, 'fee_high' => 150.00, 'threshold' => 0],
        ];

        foreach ($egFulfillmentFees as $fee) {
            DB::table('fulfillment_fees')->insert([
                'marketplace' => 'amazon.eg',
                'size_tier' => $fee['size_tier'],
                'weight_max_kg' => $fee['weight_max_kg'],
                'fee_low_price' => $fee['fee_low'],
                'fee_high_price' => $fee['fee_high'],
                'price_threshold' => $fee['threshold'],
                'effective_date' => $effectiveDate,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $this->command->info('FBA fees seeded successfully!');
    }
}

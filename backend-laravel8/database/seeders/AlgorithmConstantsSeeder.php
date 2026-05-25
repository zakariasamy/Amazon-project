<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AlgorithmConstantsSeeder extends Seeder
{
    public function run()
    {
        $version = '2025.01.01';
        $now = now();

        // Amazon US Constants
        $usConstants = [
            ['category' => 'Electronics', 'c_value' => 54000, 'p_value' => 0.65, 'cvr_value' => 0.065, 'floor' => 5, 'ceiling' => 150000],
            ['category' => 'Cell Phones & Accessories', 'c_value' => 62000, 'p_value' => 0.68, 'cvr_value' => 0.070, 'floor' => 5, 'ceiling' => 180000],
            ['category' => 'Clothing, Shoes & Jewelry', 'c_value' => 82000, 'p_value' => 0.76, 'cvr_value' => 0.100, 'floor' => 5, 'ceiling' => 200000],
            ['category' => 'Home & Kitchen', 'c_value' => 68000, 'p_value' => 0.72, 'cvr_value' => 0.120, 'floor' => 5, 'ceiling' => 160000],
            ['category' => 'Beauty & Personal Care', 'c_value' => 62000, 'p_value' => 0.70, 'cvr_value' => 0.085, 'floor' => 5, 'ceiling' => 140000],
            ['category' => 'Health & Household', 'c_value' => 58000, 'p_value' => 0.68, 'cvr_value' => 0.140, 'floor' => 5, 'ceiling' => 130000],
            ['category' => 'Sports & Outdoors', 'c_value' => 48000, 'p_value' => 0.66, 'cvr_value' => 0.100, 'floor' => 5, 'ceiling' => 100000],
            ['category' => 'Toys & Games', 'c_value' => 52000, 'p_value' => 0.70, 'cvr_value' => 0.120, 'floor' => 5, 'ceiling' => 250000],
            ['category' => 'Grocery & Gourmet Food', 'c_value' => 45000, 'p_value' => 0.60, 'cvr_value' => 0.250, 'floor' => 5, 'ceiling' => 80000],
            ['category' => 'Pet Supplies', 'c_value' => 42000, 'p_value' => 0.64, 'cvr_value' => 0.140, 'floor' => 5, 'ceiling' => 90000],
            ['category' => 'Books', 'c_value' => 35000, 'p_value' => 0.55, 'cvr_value' => 0.100, 'floor' => 3, 'ceiling' => 50000],
            ['category' => 'Office Products', 'c_value' => 38000, 'p_value' => 0.62, 'cvr_value' => 0.090, 'floor' => 5, 'ceiling' => 70000],
            ['category' => 'Tools & Home Improvement', 'c_value' => 46000, 'p_value' => 0.65, 'cvr_value' => 0.085, 'floor' => 5, 'ceiling' => 90000],
            ['category' => 'Baby', 'c_value' => 55000, 'p_value' => 0.70, 'cvr_value' => 0.130, 'floor' => 5, 'ceiling' => 120000],
            ['category' => 'default', 'c_value' => 50000, 'p_value' => 0.68, 'cvr_value' => 0.110, 'floor' => 5, 'ceiling' => 120000],
        ];

        foreach ($usConstants as $const) {
            DB::table('algorithm_constants')->insert([
                'version' => $version,
                'marketplace' => 'amazon.com',
                'category' => $const['category'],
                'c_value' => $const['c_value'],
                'p_value' => $const['p_value'],
                'cvr_value' => $const['cvr_value'],
                'floor_value' => $const['floor'],
                'ceiling_value' => $const['ceiling'],
                'market_confidence' => 0.85,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // Amazon Egypt Constants (lower confidence)
        $egConstants = [
            ['category' => 'Electronics', 'c_value' => 3200, 'p_value' => 0.58, 'cvr_value' => 0.080, 'floor' => 3, 'ceiling' => 8000],
            ['category' => 'Fashion', 'c_value' => 4500, 'p_value' => 0.62, 'cvr_value' => 0.120, 'floor' => 3, 'ceiling' => 10000],
            ['category' => 'Home & Kitchen', 'c_value' => 3800, 'p_value' => 0.60, 'cvr_value' => 0.100, 'floor' => 3, 'ceiling' => 9000],
            ['category' => 'Beauty', 'c_value' => 3500, 'p_value' => 0.55, 'cvr_value' => 0.090, 'floor' => 3, 'ceiling' => 7000],
            ['category' => 'Sports', 'c_value' => 2800, 'p_value' => 0.52, 'cvr_value' => 0.080, 'floor' => 3, 'ceiling' => 6000],
            ['category' => 'Baby Products', 'c_value' => 3000, 'p_value' => 0.55, 'cvr_value' => 0.110, 'floor' => 3, 'ceiling' => 7000],
            ['category' => 'default', 'c_value' => 3000, 'p_value' => 0.55, 'cvr_value' => 0.100, 'floor' => 3, 'ceiling' => 8000],
        ];

        foreach ($egConstants as $const) {
            DB::table('algorithm_constants')->insert([
                'version' => $version,
                'marketplace' => 'amazon.eg',
                'category' => $const['category'],
                'c_value' => $const['c_value'],
                'p_value' => $const['p_value'],
                'cvr_value' => $const['cvr_value'],
                'floor_value' => $const['floor'],
                'ceiling_value' => $const['ceiling'],
                'market_confidence' => 0.65,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $this->command->info('Algorithm constants seeded successfully!');
    }
}

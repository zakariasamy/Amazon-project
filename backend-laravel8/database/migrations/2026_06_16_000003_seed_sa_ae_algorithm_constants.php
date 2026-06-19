<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class SeedSaAeAlgorithmConstants extends Migration
{
    public function up()
    {
        $now = now();
        $version = '2025.01.01';

        // ─── Marketplace toggle settings ──────────────────────────────────────
        $toggles = [
            ['key' => 'marketplace_eg_enabled',  'value' => 'true',  'type' => 'boolean', 'category' => 'marketplaces', 'description' => 'Enable or disable Amazon Egypt (amazon.eg) marketplace.'],
            ['key' => 'marketplace_sa_enabled',  'value' => 'true',  'type' => 'boolean', 'category' => 'marketplaces', 'description' => 'Enable or disable Amazon Saudi Arabia (amazon.sa) marketplace.'],
            ['key' => 'marketplace_ae_enabled',  'value' => 'true',  'type' => 'boolean', 'category' => 'marketplaces', 'description' => 'Enable or disable Amazon UAE (amazon.ae) marketplace.'],
            ['key' => 'marketplace_com_enabled', 'value' => 'true',  'type' => 'boolean', 'category' => 'marketplaces', 'description' => 'Enable or disable Amazon USA (amazon.com) marketplace.'],
        ];

        foreach ($toggles as $toggle) {
            DB::table('app_settings')->updateOrInsert(
                ['key' => $toggle['key']],
                array_merge($toggle, ['created_at' => $now, 'updated_at' => $now])
            );
        }

        // ─── Amazon Saudi Arabia (SA) Algorithm Constants ─────────────────────
        // Modeled on Egypt constants, scaled for SA market size (~3x larger than EG)
        $saConstants = [
            ['category' => 'Electronics',       'c_value' => 9600,  'p_value' => 0.60, 'cvr_value' => 0.080, 'floor' => 3, 'ceiling' => 24000],
            ['category' => 'Fashion',           'c_value' => 13500, 'p_value' => 0.64, 'cvr_value' => 0.120, 'floor' => 3, 'ceiling' => 30000],
            ['category' => 'Home & Kitchen',    'c_value' => 11400, 'p_value' => 0.62, 'cvr_value' => 0.100, 'floor' => 3, 'ceiling' => 27000],
            ['category' => 'Beauty',            'c_value' => 10500, 'p_value' => 0.57, 'cvr_value' => 0.090, 'floor' => 3, 'ceiling' => 21000],
            ['category' => 'Sports',            'c_value' => 8400,  'p_value' => 0.54, 'cvr_value' => 0.080, 'floor' => 3, 'ceiling' => 18000],
            ['category' => 'Baby Products',     'c_value' => 9000,  'p_value' => 0.57, 'cvr_value' => 0.110, 'floor' => 3, 'ceiling' => 21000],
            ['category' => 'default',           'c_value' => 9000,  'p_value' => 0.57, 'cvr_value' => 0.100, 'floor' => 3, 'ceiling' => 24000],
        ];

        foreach ($saConstants as $const) {
            DB::table('algorithm_constants')->insert([
                'version'          => $version,
                'marketplace'      => 'amazon.sa',
                'category'         => $const['category'],
                'c_value'          => $const['c_value'],
                'p_value'          => $const['p_value'],
                'cvr_value'        => $const['cvr_value'],
                'floor_value'      => $const['floor'],
                'ceiling_value'    => $const['ceiling'],
                'market_confidence'=> 0.60,
                'is_active'        => true,
                'created_at'       => $now,
                'updated_at'       => $now,
            ]);
        }

        // ─── Amazon UAE (AE) Algorithm Constants ─────────────────────────────
        // UAE is a high-income, smaller market — between SA and US in scale
        $aeConstants = [
            ['category' => 'Electronics',       'c_value' => 8000,  'p_value' => 0.60, 'cvr_value' => 0.080, 'floor' => 3, 'ceiling' => 20000],
            ['category' => 'Fashion',           'c_value' => 11000, 'p_value' => 0.64, 'cvr_value' => 0.120, 'floor' => 3, 'ceiling' => 25000],
            ['category' => 'Home & Kitchen',    'c_value' => 9500,  'p_value' => 0.62, 'cvr_value' => 0.100, 'floor' => 3, 'ceiling' => 22000],
            ['category' => 'Beauty',            'c_value' => 8800,  'p_value' => 0.57, 'cvr_value' => 0.090, 'floor' => 3, 'ceiling' => 17500],
            ['category' => 'Sports',            'c_value' => 7000,  'p_value' => 0.54, 'cvr_value' => 0.080, 'floor' => 3, 'ceiling' => 15000],
            ['category' => 'Baby Products',     'c_value' => 7500,  'p_value' => 0.57, 'cvr_value' => 0.110, 'floor' => 3, 'ceiling' => 17500],
            ['category' => 'default',           'c_value' => 7500,  'p_value' => 0.57, 'cvr_value' => 0.100, 'floor' => 3, 'ceiling' => 20000],
        ];

        foreach ($aeConstants as $const) {
            DB::table('algorithm_constants')->insert([
                'version'          => $version,
                'marketplace'      => 'amazon.ae',
                'category'         => $const['category'],
                'c_value'          => $const['c_value'],
                'p_value'          => $const['p_value'],
                'cvr_value'        => $const['cvr_value'],
                'floor_value'      => $const['floor'],
                'ceiling_value'    => $const['ceiling'],
                'market_confidence'=> 0.60,
                'is_active'        => true,
                'created_at'       => $now,
                'updated_at'       => $now,
            ]);
        }
    }

    public function down()
    {
        DB::table('algorithm_constants')
            ->whereIn('marketplace', ['amazon.sa', 'amazon.ae'])
            ->delete();

        DB::table('app_settings')
            ->whereIn('key', ['marketplace_eg_enabled', 'marketplace_sa_enabled', 'marketplace_ae_enabled', 'marketplace_com_enabled'])
            ->delete();
    }
}

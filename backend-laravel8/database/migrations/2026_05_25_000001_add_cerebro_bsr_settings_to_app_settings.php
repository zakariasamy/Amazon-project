<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddCerebroBsrSettingsToAppSettings extends Migration
{
    public function up()
    {
        $settings = [
            [
                'key' => 'cerebro_fetch_bsr_enabled',
                'value' => 'true',
                'type' => 'boolean',
                'description' => '[Competitor Keyword Analyzer] Fetch product pages for BSR per keyword SERP. More accurate volume, slower analysis.',
            ],
            [
                'key' => 'cerebro_bsr_products_limit',
                'value' => '20',
                'type' => 'integer',
                'description' => '[Competitor Keyword Analyzer] Max SERP products to fetch BSR for per keyword. Match Search Page Market Analysis for closest volume parity.',
            ],
            [
                'key' => 'cerebro_bsr_parallel_requests',
                'value' => '3',
                'type' => 'integer',
                'description' => '[Competitor Keyword Analyzer] Parallel product page fetches for BSR.',
            ],
            [
                'key' => 'cerebro_bsr_delay_ms',
                'value' => '500',
                'type' => 'integer',
                'description' => '[Competitor Keyword Analyzer] Delay (ms) between BSR fetch batches.',
            ],
            [
                'key' => 'cerebro_search_delay_ms',
                'value' => '500',
                'type' => 'integer',
                'description' => '[Competitor Keyword Analyzer] Delay (ms) between keyword SERP searches.',
            ],
        ];

        foreach ($settings as $setting) {
            DB::table('app_settings')->updateOrInsert(
                ['key' => $setting['key']],
                array_merge($setting, [
                    'category' => 'cerebro',
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }

    public function down()
    {
        DB::table('app_settings')
            ->whereIn('key', [
                'cerebro_fetch_bsr_enabled',
                'cerebro_bsr_products_limit',
                'cerebro_bsr_parallel_requests',
                'cerebro_bsr_delay_ms',
                'cerebro_search_delay_ms',
            ])
            ->delete();
    }
}

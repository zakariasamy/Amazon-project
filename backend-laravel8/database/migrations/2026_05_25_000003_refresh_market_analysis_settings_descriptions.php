<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class RefreshMarketAnalysisSettingsDescriptions extends Migration
{
    public function up()
    {
        $settings = [
            'search_page_products_limit' => [
                'value' => '20',
                'type' => 'integer',
                'description' => '[Market Analysis] Max search-page products to fetch BSR for. Higher = closer sales/volume estimate, slower analysis.',
            ],
            'search_page_bsr_parallel_requests' => [
                'value' => '5',
                'type' => 'integer',
                'description' => '[Market Analysis] Parallel product page fetches for BSR.',
            ],
            'search_page_bsr_delay_ms' => [
                'value' => '300',
                'type' => 'integer',
                'description' => '[Market Analysis] Delay in milliseconds between BSR fetch batches.',
            ],
        ];

        foreach ($settings as $key => $setting) {
            $exists = DB::table('app_settings')->where('key', $key)->exists();

            DB::table('app_settings')->updateOrInsert(
                ['key' => $key],
                [
                    'value' => $exists ? DB::raw('value') : $setting['value'],
                    'type' => $setting['type'],
                    'description' => $setting['description'],
                    'category' => 'search_page',
                    'created_at' => $exists ? DB::raw('created_at') : now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    public function down()
    {
        //
    }
}

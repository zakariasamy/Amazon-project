<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddFeatureAccessSettingsToAppSettings extends Migration
{
    public function up()
    {
        $settings = [
            [
                'key' => 'feature_market_analysis_enabled',
                'value' => 'true',
                'type' => 'boolean',
                'description' => 'Enable or disable the Market Analysis feature on search pages.',
                'category' => 'features',
            ],
            [
                'key' => 'feature_keyword_analyzer_pro_enabled',
                'value' => 'true',
                'type' => 'boolean',
                'description' => 'Enable or disable the Competitor Keyword Analyzer (Keyword Analyzer Pro) feature on search pages.',
                'category' => 'features',
            ],
            [
                'key' => 'feature_analyze_product_enabled',
                'value' => 'true',
                'type' => 'boolean',
                'description' => 'Enable or disable the Analyze Product feature on product pages.',
                'category' => 'features',
            ],
            [
                'key' => 'feature_reverse_asin_enabled',
                'value' => 'true',
                'type' => 'boolean',
                'description' => 'Enable or disable the Reverse ASIN feature on product pages.',
                'category' => 'features',
            ],
            [
                'key' => 'feature_fba_calculator_enabled',
                'value' => 'true',
                'type' => 'boolean',
                'description' => 'Enable or disable the FBA Calculator feature on product pages.',
                'category' => 'features',
            ],
        ];

        foreach ($settings as $setting) {
            DB::table('app_settings')->updateOrInsert(
                ['key' => $setting['key']],
                array_merge($setting, [
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
                'feature_market_analysis_enabled',
                'feature_keyword_analyzer_pro_enabled',
                'feature_analyze_product_enabled',
                'feature_reverse_asin_enabled',
                'feature_fba_calculator_enabled',
            ])
            ->delete();
    }
}

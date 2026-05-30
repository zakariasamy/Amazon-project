<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AlignCerebroBsrLimitWithMarketAnalysis extends Migration
{
    public function up()
    {
        DB::table('app_settings')
            ->where('key', 'cerebro_bsr_products_limit')
            ->where('value', '10')
            ->update([
                'value' => '20',
                'description' => '[Competitor Keyword Analyzer] Max SERP products to fetch BSR for per keyword. Match Search Page Market Analysis for closest volume parity.',
                'updated_at' => now(),
            ]);
    }

    public function down()
    {
        DB::table('app_settings')
            ->where('key', 'cerebro_bsr_products_limit')
            ->where('value', '20')
            ->update([
                'value' => '10',
                'updated_at' => now(),
            ]);
    }
}

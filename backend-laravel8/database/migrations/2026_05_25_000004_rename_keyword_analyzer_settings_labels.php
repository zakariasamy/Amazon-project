<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class RenameKeywordAnalyzerSettingsLabels extends Migration
{
    public function up()
    {
        $descriptions = [
            'cerebro_fetch_bsr_enabled' => '[Competitor Keyword Analyzer] Fetch product pages for BSR per keyword SERP. More accurate volume, slower analysis.',
            'cerebro_bsr_products_limit' => '[Competitor Keyword Analyzer] Max SERP products to fetch BSR for per keyword. Match Search Page Market Analysis for closest volume parity.',
            'cerebro_bsr_parallel_requests' => '[Competitor Keyword Analyzer] Parallel product page fetches for BSR.',
            'cerebro_bsr_delay_ms' => '[Competitor Keyword Analyzer] Delay (ms) between BSR fetch batches.',
            'cerebro_search_delay_ms' => '[Competitor Keyword Analyzer] Delay (ms) between keyword SERP searches.',
        ];

        foreach ($descriptions as $key => $description) {
            DB::table('app_settings')
                ->where('key', $key)
                ->update([
                    'description' => $description,
                    'updated_at' => now(),
                ]);
        }
    }

    public function down()
    {
        //
    }
}

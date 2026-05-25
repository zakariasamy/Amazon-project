<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateAppSettingsTable extends Migration
{
    public function up()
    {
        Schema::create('app_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('value');
            $table->string('type')->default('integer'); // integer, string, boolean, json
            $table->text('description')->nullable();
            $table->string('category')->default('general'); // general, scraping, rate_limit, etc.
            $table->timestamps();
        });

        // Insert default settings with clear naming and descriptions
        DB::table('app_settings')->insert([
            
            // =====================================================
            // SEARCH PAGE ANALYSIS - BSR Enrichment Settings
            // These control how the Search Page fetches BSR data
            // =====================================================
            [
                'key' => 'search_page_products_limit',
                'value' => '20',
                'type' => 'integer',
                'description' => '[Search Page] Max products to fetch BSR for when analyzing a search results page. Higher = more accurate but slower.',
                'category' => 'search_page',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'search_page_bsr_parallel_requests',
                'value' => '5',
                'type' => 'integer',
                'description' => '[Search Page] How many product pages to fetch in parallel for BSR. Lower = safer from rate limits.',
                'category' => 'search_page',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'search_page_bsr_delay_ms',
                'value' => '300',
                'type' => 'integer',
                'description' => '[Search Page] Delay (ms) between BSR fetch batches. Higher = slower but safer.',
                'category' => 'search_page',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // =====================================================
            // REVERSE ASIN - BSR Enrichment Settings  
            // These control how Reverse ASIN fetches BSR for each keyword
            // =====================================================
            [
                'key' => 'reverse_asin_products_limit',
                'value' => '10',
                'type' => 'integer',
                'description' => '[Reverse ASIN] Max products to fetch BSR for PER KEYWORD. Lower = faster (processes many keywords).',
                'category' => 'reverse_asin',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'reverse_asin_bsr_parallel_requests',
                'value' => '3',
                'type' => 'integer',
                'description' => '[Reverse ASIN] Parallel product page fetches for BSR per keyword. Keep low to avoid rate limits.',
                'category' => 'reverse_asin',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'reverse_asin_bsr_delay_ms',
                'value' => '500',
                'type' => 'integer',
                'description' => '[Reverse ASIN] Delay (ms) between BSR fetch batches per keyword.',
                'category' => 'reverse_asin',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // =====================================================
            // REVERSE ASIN - Keyword Search Settings
            // These control how Reverse ASIN searches Amazon for keywords
            // =====================================================
            [
                'key' => 'reverse_asin_keywords_limit',
                'value' => '50',
                'type' => 'integer',
                'description' => '[Reverse ASIN] Maximum keywords to process in one analysis session.',
                'category' => 'reverse_asin',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'reverse_asin_search_delay_ms',
                'value' => '1500',
                'type' => 'integer',
                'description' => '[Reverse ASIN] Delay (ms) between Amazon keyword searches to avoid captcha.',
                'category' => 'reverse_asin',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'reverse_asin_backend_batch_size',
                'value' => '5',
                'type' => 'integer',
                'description' => '[Reverse ASIN] Keywords to batch per backend API call for volume estimation.',
                'category' => 'reverse_asin',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('app_settings');
    }
}

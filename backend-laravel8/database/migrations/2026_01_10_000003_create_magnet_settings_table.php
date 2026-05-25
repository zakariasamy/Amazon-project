<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateMagnetSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('magnet_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->text('value');
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // Insert default settings
        DB::table('magnet_settings')->insert([
            [
                'key' => 'attribute_product_count',
                'value' => '5',
                'description' => 'Number of top products to scrape for attribute-based keywords',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'use_google_suggestions',
                'value' => 'true',
                'description' => 'Enable Google autocomplete for global research',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'use_bing_suggestions',
                'value' => 'true',
                'description' => 'Enable Bing autocomplete for global research',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'use_youtube_suggestions',
                'value' => 'true',
                'description' => 'Enable YouTube autocomplete for global research',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('magnet_settings');
    }
}

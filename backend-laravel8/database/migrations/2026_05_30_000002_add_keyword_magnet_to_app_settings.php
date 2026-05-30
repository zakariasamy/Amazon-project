<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddKeywordMagnetToAppSettings extends Migration
{
    public function up()
    {
        DB::table('app_settings')->updateOrInsert(
            ['key' => 'feature_keyword_magnet_enabled'],
            [
                'value' => 'true',
                'type' => 'boolean',
                'description' => 'Enable or disable the Keyword Magnet feature on search pages.',
                'category' => 'features',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    public function down()
    {
        DB::table('app_settings')
            ->where('key', 'feature_keyword_magnet_enabled')
            ->delete();
    }
}

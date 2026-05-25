<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSearchVolumeColumnsToKeywordCacheTable extends Migration
{
    public function up()
    {
        Schema::table('keyword_cache', function (Blueprint $table) {
            if (!Schema::hasColumn('keyword_cache', 'search_volume_estimate')) {
                $table->unsignedInteger('search_volume_estimate')->nullable()->after('category');
            }
            if (!Schema::hasColumn('keyword_cache', 'difficulty_score')) {
                $table->unsignedInteger('difficulty_score')->nullable()->after('search_volume_estimate');
            }
        });
    }

    public function down()
    {
        Schema::table('keyword_cache', function (Blueprint $table) {
            $table->dropColumn(['search_volume_estimate', 'difficulty_score']);
        });
    }
}

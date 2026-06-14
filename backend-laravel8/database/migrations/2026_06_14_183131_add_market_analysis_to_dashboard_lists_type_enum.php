<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddMarketAnalysisToDashboardListsTypeEnum extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE dashboard_lists MODIFY COLUMN type ENUM('products', 'keyword_magnet', 'competitor_keyword_analyzer', 'reverse_asin', 'market_analysis') NOT NULL");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE dashboard_lists MODIFY COLUMN type ENUM('products', 'keyword_magnet', 'competitor_keyword_analyzer', 'reverse_asin') NOT NULL");
    }
}

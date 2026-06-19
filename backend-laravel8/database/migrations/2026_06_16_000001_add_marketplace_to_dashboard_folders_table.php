<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddMarketplaceToDashboardFoldersTable extends Migration
{
    public function up()
    {
        Schema::table('dashboard_folders', function (Blueprint $table) {
            $table->string('marketplace', 30)->default('amazon.eg')->after('description');
            $table->index(['user_id', 'marketplace']);
        });

        // Backfill existing folders to amazon.eg
        DB::table('dashboard_folders')->whereNull('marketplace')->update(['marketplace' => 'amazon.eg']);
    }

    public function down()
    {
        Schema::table('dashboard_folders', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'marketplace']);
            $table->dropColumn('marketplace');
        });
    }
}

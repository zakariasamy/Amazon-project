<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexToProductCacheTable extends Migration
{
    public function up()
    {
        Schema::table('product_cache', function (Blueprint $table) {
            // Add standalone marketplace index to speed up marketplace-only queries
            // (the composite unique on [asin, marketplace] already covers ASIN+marketplace lookups)
            $table->index('marketplace', 'product_cache_marketplace_index');
        });
    }

    public function down()
    {
        Schema::table('product_cache', function (Blueprint $table) {
            $table->dropIndex('product_cache_marketplace_index');
        });
    }
}

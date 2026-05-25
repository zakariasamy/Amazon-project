<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSearchAnalysesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('search_analyses', function (Blueprint $table) {
            $table->id();
            $table->string('marketplace', 30);
            $table->string('keyword', 255);
            $table->integer('search_volume')->nullable();
            $table->string('demand_level', 20)->nullable();
            $table->integer('products_count')->default(0);
            $table->json('products_data')->nullable(); // Store full enriched products list
            $table->timestamps();

            $table->index(['marketplace', 'keyword']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('search_analyses');
    }
}

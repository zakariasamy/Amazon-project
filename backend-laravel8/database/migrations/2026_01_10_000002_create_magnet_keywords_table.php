<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMagnetKeywordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('magnet_keywords', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('analysis_id');
            $table->string('keyword', 500);
            $table->tinyInteger('word_count')->default(1);
            
            // Volume & Opportunity
            $table->integer('search_volume')->default(0);
            $table->decimal('magnet_iq_score', 8, 2)->default(0);
            $table->integer('competing_products')->default(0);
            $table->tinyInteger('title_density')->default(0);
            
            // CPR Calculations
            $table->integer('cpr_8day')->default(0);
            $table->integer('cpr_total')->default(0);
            
            // Market Data
            $table->integer('keyword_sales')->default(0);
            $table->decimal('avg_price', 10, 2)->default(0);
            $table->integer('avg_reviews')->default(0);
            $table->tinyInteger('sponsored_count')->default(0);
            
            // Discovery Metadata
            $table->string('match_type', 50)->default('autocomplete'); // autocomplete, related, title, attribute, google, bing, youtube
            $table->string('source_domain', 100)->nullable(); // e.g., amazon.eg, google.com
            $table->tinyInteger('relevance_score')->default(0);
            
            $table->timestamps();

            $table->foreign('analysis_id')->references('id')->on('magnet_analyses')->onDelete('cascade');
            $table->index(['analysis_id', 'magnet_iq_score']);
            $table->index(['analysis_id', 'search_volume']);
            $table->index('keyword');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('magnet_keywords');
    }
}

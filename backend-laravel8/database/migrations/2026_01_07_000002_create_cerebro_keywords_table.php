<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCerebroKeywordsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('cerebro_keywords', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('analysis_id');
            $table->string('keyword', 500);
            $table->tinyInteger('word_count')->default(1);

            // Volume & Opportunity
            $table->integer('search_volume')->default(0);
            $table->decimal('search_volume_trend', 5, 2)->nullable();
            $table->decimal('cerebro_iq_score', 8, 2)->default(0);
            $table->integer('competing_products')->default(0);
            $table->tinyInteger('title_density')->default(0);

            // CPR Calculations
            $table->integer('cpr_8day')->default(0);
            $table->integer('cpr_total')->default(0);

            // Sales
            $table->integer('keyword_sales')->default(0);

            // Sponsored
            $table->tinyInteger('sponsored_asin_count')->default(0);

            // Per-ASIN Rankings (JSON)
            $table->json('organic_ranks')->nullable();
            $table->json('sponsored_ranks')->nullable();

            // Aggregates
            $table->tinyInteger('asins_ranking')->default(0);
            $table->decimal('avg_organic_rank', 5, 1)->nullable();
            $table->smallInteger('min_organic_rank')->nullable();
            $table->smallInteger('max_organic_rank')->nullable();

            // Flags
            $table->boolean('has_amazon_choice')->default(false);
            $table->enum('match_type', ['organic', 'sponsored', 'both', 'amazon_rec'])->default('organic');

            $table->timestamps();

            $table->foreign('analysis_id')->references('id')->on('cerebro_analyses')->onDelete('cascade');
            $table->index(['analysis_id', 'cerebro_iq_score']);
            $table->index(['analysis_id', 'search_volume']);
            $table->index('keyword', 'idx_keyword_prefix');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('cerebro_keywords');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCerebroFolderKeywordsTable extends Migration
{
    public function up()
    {
        Schema::create('cerebro_folder_keywords', function (Blueprint $table) {
            $table->id();
            $table->foreignId('folder_id')->constrained('cerebro_folders')->onDelete('cascade');
            $table->string('keyword');
            $table->integer('search_volume')->default(0);
            $table->decimal('cerebro_iq_score', 5, 2)->default(0);
            $table->integer('cpr_8day')->nullable();
            $table->integer('word_count')->default(1);
            $table->integer('competing_products')->default(0);
            $table->decimal('title_density', 5, 2)->nullable();
            $table->json('organic_ranks')->nullable(); // {asin: position}
            $table->json('sponsored_ranks')->nullable();
            $table->string('source')->default('manual'); // manual, analysis, csv_import
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['folder_id', 'keyword']);
            $table->index('search_volume');
            $table->index('cerebro_iq_score');
        });
    }

    public function down()
    {
        Schema::dropIfExists('cerebro_folder_keywords');
    }
}

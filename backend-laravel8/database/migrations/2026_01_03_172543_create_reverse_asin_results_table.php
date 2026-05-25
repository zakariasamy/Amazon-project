<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReverseAsinResultsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reverse_asin_results', function (Blueprint $table) {
            $table->id();
            $table->string('asin', 20);
            $table->string('marketplace', 30);
            $table->string('title', 500)->nullable();
            $table->string('category', 200)->nullable();
            $table->integer('keywords_tested')->default(0);
            $table->integer('keywords_found')->default(0);
            $table->json('keywords_data')->nullable(); // Full keyword ranking list
            $table->string('source', 50)->default('carousel_analysis');
            $table->timestamps();

            $table->index(['asin', 'marketplace']);
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
        Schema::dropIfExists('reverse_asin_results');
    }
}

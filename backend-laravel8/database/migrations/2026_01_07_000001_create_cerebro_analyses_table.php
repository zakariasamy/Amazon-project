<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCerebroAnalysesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('cerebro_analyses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('name')->nullable();
            $table->string('marketplace', 30)->default('amazon.com');
            $table->json('asins');
            $table->tinyInteger('asin_count')->default(1);
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->integer('total_keywords')->default(0);
            $table->tinyInteger('progress_percent')->default(0);
            $table->text('error_message')->nullable();
            $table->integer('duration_seconds')->nullable();
            $table->timestamps();
            $table->timestamp('completed_at')->nullable();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['user_id', 'status']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('cerebro_analyses');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalesFeedbackTable extends Migration
{
    public function up()
    {
        Schema::create('sales_feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('asin', 20);
            $table->string('marketplace', 30);
            $table->string('category', 100);
            $table->integer('bsr');
            $table->integer('estimated_sales');
            $table->integer('actual_sales');
            $table->integer('actual_sales_normalized'); // 30-day equivalent
            $table->unsignedTinyInteger('sales_window_days')->default(30);
            $table->decimal('error_percent', 6, 2);
            $table->enum('monthly_sales_source', ['badge', 'bsr_estimate', 'user_feedback', 'hybrid'])->default('user_feedback');
            $table->timestamps();

            $table->index('user_id');
            $table->index(['marketplace', 'category', 'created_at']);
            $table->index('asin');
        });
    }

    public function down()
    {
        Schema::dropIfExists('sales_feedback');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductCacheTable extends Migration
{
    public function up()
    {
        Schema::create('product_cache', function (Blueprint $table) {
            $table->id();
            $table->string('asin', 20);
            $table->string('marketplace', 30);
            $table->string('title', 500)->nullable();
            $table->string('category', 200)->nullable();
            $table->integer('bsr')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->integer('monthly_badge_value')->nullable();
            $table->integer('monthly_sales_estimate')->nullable();
            $table->enum('monthly_sales_source', ['badge', 'bsr_estimate', 'user_feedback', 'hybrid'])->nullable();
            $table->timestamp('last_scraped_at')->nullable();
            $table->timestamps();

            $table->unique(['asin', 'marketplace']);
            $table->index(['category', 'bsr']);
            $table->index('monthly_sales_source');
        });
    }

    public function down()
    {
        Schema::dropIfExists('product_cache');
    }
}

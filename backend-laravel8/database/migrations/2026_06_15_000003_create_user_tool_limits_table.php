<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserToolLimitsTable extends Migration
{
    public function up()
    {
        Schema::create('user_tool_limits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subscription_id');
            $table->unsignedBigInteger('user_id');
            $table->string('tool_name'); // e.g. 'market_analysis', 'keyword_magnet', 'reverse_asin', 'fba_calculator', 'cerebro', 'analyze_product', 'search_volume'
            $table->integer('limit_count')->default(-1); // -1 = unlimited
            $table->integer('bonus_count')->default(0);  // admin-granted extra
            $table->integer('used_count')->default(0);
            $table->timestamp('next_reset_at')->nullable();
            $table->timestamps();

            $table->foreign('subscription_id')->references('id')->on('subscriptions')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['subscription_id', 'tool_name']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_tool_limits');
    }
}

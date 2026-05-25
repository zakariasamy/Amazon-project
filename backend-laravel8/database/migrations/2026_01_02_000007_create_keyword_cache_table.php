<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKeywordCacheTable extends Migration
{
    public function up()
    {
        Schema::create('keyword_cache', function (Blueprint $table) {
            $table->id();
            $table->string('marketplace', 30);
            $table->string('keyword', 255);
            $table->string('category', 100)->nullable();
            $table->unsignedInteger('search_volume_estimate')->nullable();
            $table->unsignedInteger('difficulty_score')->nullable();
            $table->unsignedInteger('search_count')->default(1);
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();

            $table->unique(['marketplace', 'keyword']);
            $table->index(['marketplace', 'search_count']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('keyword_cache');
    }
}

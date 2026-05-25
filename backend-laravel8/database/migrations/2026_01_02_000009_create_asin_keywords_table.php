<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAsinKeywordsTable extends Migration
{
    public function up()
    {
        Schema::create('asin_keywords', function (Blueprint $table) {
            $table->id();
            $table->string('asin', 20);
            $table->string('marketplace', 30);
            $table->string('keyword', 255);
            $table->unsignedTinyInteger('position')->default(1);
            $table->boolean('is_sponsored')->default(false);
            $table->unsignedTinyInteger('page')->default(1);
            $table->unsignedInteger('times_seen')->default(1);
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();

            $table->unique(['asin', 'marketplace', 'keyword']);
            $table->index(['asin', 'marketplace']);
            $table->index(['keyword', 'marketplace']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('asin_keywords');
    }
}

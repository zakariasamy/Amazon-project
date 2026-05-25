<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSeasonalityFactorsTable extends Migration
{
    public function up()
    {
        Schema::create('seasonality_factors', function (Blueprint $table) {
            $table->id();
            $table->string('marketplace', 30);
            $table->unsignedTinyInteger('month');
            $table->decimal('multiplier', 4, 2)->default(1.00);
            $table->integer('year');
            $table->string('notes', 255)->nullable();
            $table->timestamps();

            $table->unique(['marketplace', 'year', 'month']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('seasonality_factors');
    }
}

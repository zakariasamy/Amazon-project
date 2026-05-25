<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAlgorithmConstantsTable extends Migration
{
    public function up()
    {
        Schema::create('algorithm_constants', function (Blueprint $table) {
            $table->id();
            $table->string('version', 20);
            $table->string('marketplace', 30);
            $table->string('category', 100);
            $table->decimal('c_value', 12, 2);
            $table->decimal('p_value', 5, 3);
            $table->decimal('cvr_value', 5, 3);
            $table->integer('floor_value');
            $table->integer('ceiling_value');
            $table->decimal('market_confidence', 3, 2)->default(0.85);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['marketplace', 'category']);
            $table->index('version');
        });
    }

    public function down()
    {
        Schema::dropIfExists('algorithm_constants');
    }
}

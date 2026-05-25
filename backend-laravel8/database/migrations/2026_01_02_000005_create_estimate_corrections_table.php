<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEstimateCorrectionsTable extends Migration
{
    public function up()
    {
        Schema::create('estimate_corrections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('asin', 20);
            $table->string('marketplace', 30);
            $table->string('field', 50);
            $table->string('original_value', 255);
            $table->string('corrected_value', 255);
            $table->string('reason', 500)->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('asin');
        });
    }

    public function down()
    {
        Schema::dropIfExists('estimate_corrections');
    }
}

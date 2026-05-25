<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCalibrationLogTable extends Migration
{
    public function up()
    {
        Schema::create('calibration_log', function (Blueprint $table) {
            $table->id();
            $table->string('marketplace', 30);
            $table->string('category', 100);
            $table->decimal('previous_c', 12, 2);
            $table->decimal('new_c', 12, 2);
            $table->decimal('avg_error_percent', 6, 2);
            $table->integer('sample_count');
            $table->timestamp('applied_at')->useCurrent();

            $table->index(['marketplace', 'category', 'applied_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('calibration_log');
    }
}

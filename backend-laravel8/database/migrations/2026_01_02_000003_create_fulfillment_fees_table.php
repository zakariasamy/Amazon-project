<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFulfillmentFeesTable extends Migration
{
    public function up()
    {
        Schema::create('fulfillment_fees', function (Blueprint $table) {
            $table->id();
            $table->string('marketplace', 30);
            $table->string('size_tier', 50);
            $table->decimal('weight_max_kg', 5, 2);
            $table->decimal('fee_low_price', 10, 2);
            $table->decimal('fee_high_price', 10, 2);
            $table->decimal('price_threshold', 10, 2)->default(0);
            $table->date('effective_date');
            $table->timestamps();

            $table->index(['marketplace', 'size_tier']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('fulfillment_fees');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFbaFeesTable extends Migration
{
    public function up()
    {
        Schema::create('fba_fees', function (Blueprint $table) {
            $table->id();
            $table->string('marketplace', 30);
            $table->string('category', 100);
            $table->decimal('referral_fee_percent', 5, 2);
            $table->decimal('referral_fee_min', 10, 2)->default(0);
            $table->date('effective_date');
            $table->date('expiry_date')->nullable();
            $table->boolean('is_promotional')->default(false);
            $table->timestamps();

            $table->index(['marketplace', 'effective_date']);
            $table->index(['marketplace', 'category']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('fba_fees');
    }
}

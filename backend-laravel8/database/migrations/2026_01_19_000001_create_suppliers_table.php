<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSuppliersTable extends Migration
{
    public function up()
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('name_ar');
            $table->enum('category', [
                'electronics', 'tools', 'car_accessories', 'fashion', 
                'home', 'sports', 'toys', 'general'
            ])->default('general');
            $table->text('description')->nullable();
            $table->text('description_ar')->nullable();
            $table->string('phone')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('telegram_username')->nullable();
            $table->string('telegram_group_link')->nullable();
            $table->string('website')->nullable();
            $table->string('location')->nullable();
            $table->string('location_ar')->nullable();
            $table->string('logo')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_active')->default(true);
            $table->json('verification_documents')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('suppliers');
    }
}

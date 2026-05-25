<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSupplierApplicationsTable extends Migration
{
    public function up()
    {
        Schema::create('supplier_applications', function (Blueprint $table) {
            $table->id();
            $table->string('supplier_name');
            $table->string('supplier_name_ar');
            $table->string('applicant_email');
            $table->string('applicant_phone');
            $table->enum('category', [
                'electronics', 'tools', 'car_accessories', 'fashion', 
                'home', 'sports', 'toys', 'general'
            ])->default('general');
            $table->text('description')->nullable();
            $table->text('description_ar')->nullable();
            $table->string('telegram_group_link')->nullable();
            $table->string('website')->nullable();
            $table->string('location')->nullable();
            $table->string('location_ar')->nullable();
            $table->json('proof_documents')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('admin_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('supplier_applications');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVideoToSupplierProductsTable extends Migration
{
    public function up()
    {
        Schema::table('supplier_products', function (Blueprint $table) {
            $table->string('video')->nullable()->after('images');
            $table->text('specifications')->nullable()->after('description_ar');
            $table->text('specifications_ar')->nullable()->after('specifications');
        });
    }

    public function down()
    {
        Schema::table('supplier_products', function (Blueprint $table) {
            $table->dropColumn(['video', 'specifications', 'specifications_ar']);
        });
    }
}

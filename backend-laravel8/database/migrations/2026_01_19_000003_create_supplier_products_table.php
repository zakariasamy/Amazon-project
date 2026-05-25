<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSupplierProductsTable extends Migration
{
    public function up()
    {
        Schema::create('supplier_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('name_ar');
            $table->string('sku')->nullable();
            $table->enum('category', [
                'electronics', 'tools', 'car_accessories', 'fashion', 
                'home', 'sports', 'toys', 'general'
            ])->default('general');
            $table->text('description')->nullable();
            $table->text('description_ar')->nullable();
            $table->json('images')->nullable(); // Array of image paths
            $table->integer('min_order_quantity')->default(1);
            $table->json('price_tiers')->nullable(); // [{min_qty: 1, max_qty: 10, price: 100}, ...]
            $table->decimal('base_price', 10, 2)->nullable(); // Price for minimum order
            $table->string('unit')->default('piece'); // piece, box, carton, kg, etc.
            $table->string('unit_ar')->default('قطعة');
            $table->boolean('is_available')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->integer('stock_quantity')->nullable();
            $table->string('origin_country')->nullable();
            $table->string('origin_country_ar')->nullable();
            $table->timestamps();
            
            $table->index(['supplier_id', 'category']);
            $table->index('is_available');
        });
    }

    public function down()
    {
        Schema::dropIfExists('supplier_products');
    }
}

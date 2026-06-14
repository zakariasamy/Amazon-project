<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDashboardListItemsTable extends Migration
{
    public function up()
    {
        Schema::create('dashboard_list_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('list_id')->constrained('dashboard_lists')->onDelete('cascade');
            $table->json('data'); // full row payload from the tool
            $table->timestamps();

            $table->index('list_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('dashboard_list_items');
    }
}

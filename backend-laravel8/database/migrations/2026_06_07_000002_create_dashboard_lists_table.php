<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDashboardListsTable extends Migration
{
    public function up()
    {
        Schema::create('dashboard_lists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('folder_id')->constrained('dashboard_folders')->onDelete('cascade');
            $table->string('name', 100);
            $table->enum('type', [
                'products',
                'keyword_magnet',
                'competitor_keyword_analyzer',
                'reverse_asin',
            ]);
            $table->text('description')->nullable();
            $table->unsignedInteger('item_count')->default(0);
            $table->timestamps();

            $table->index(['user_id', 'folder_id']);
            $table->index(['folder_id', 'type']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('dashboard_lists');
    }
}

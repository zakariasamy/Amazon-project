<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDashboardFoldersTable extends Migration
{
    public function up()
    {
        Schema::create('dashboard_folders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('parent_id')->nullable(); // null = root folder
            $table->string('name', 100);
            $table->string('color', 20)->default('#6366f1');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'parent_id']);
            $table->index(['user_id', 'name']);

            // Self-referencing FK added after table creation to avoid ordering issues
            $table->foreign('parent_id')
                  ->references('id')
                  ->on('dashboard_folders')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('dashboard_folders');
    }
}

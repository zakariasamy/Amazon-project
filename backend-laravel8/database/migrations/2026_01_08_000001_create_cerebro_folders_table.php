<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCerebroFoldersTable extends Migration
{
    public function up()
    {
        Schema::create('cerebro_folders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('color')->default('#6366f1'); // Folder color for UI
            $table->text('description')->nullable();
            $table->integer('keyword_count')->default(0);
            $table->timestamps();
            
            $table->index(['user_id', 'name']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('cerebro_folders');
    }
}

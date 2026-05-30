<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class ChangeValueToTextInAppSettings extends Migration
{
    public function up()
    {
        // Alter value column to TEXT using raw SQL to avoid doctrine/dbal dependency
        DB::statement('ALTER TABLE app_settings MODIFY value TEXT');
    }

    public function down()
    {
        DB::statement('ALTER TABLE app_settings MODIFY value VARCHAR(255)');
    }
}

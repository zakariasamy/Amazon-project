<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTrialDaysToPricingPlansTable extends Migration
{
    public function up()
    {
        Schema::table('pricing_plans', function (Blueprint $table) {
            $table->integer('trial_days')->default(0)->after('billing_cycle');
        });
    }

    public function down()
    {
        Schema::table('pricing_plans', function (Blueprint $table) {
            $table->dropColumn('trial_days');
        });
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddInstapaySettingsToAppSettings extends Migration
{
    public function up()
    {
        $now = now();
        $settings = [
            [
                'key'         => 'instapay_username',
                'value'       => 'amazon.analyzer@instapay',
                'type'        => 'string',
                'description' => 'InstaPay username for receiving subscription payments',
                'category'    => 'payments',
            ],
            [
                'key'         => 'instapay_phone',
                'value'       => '',
                'type'        => 'string',
                'description' => 'InstaPay phone number (optional)',
                'category'    => 'payments',
            ],
            [
                'key'         => 'instapay_instructions',
                'value'       => 'Send the exact subscription amount to the InstaPay username above, then upload a clear screenshot of the payment confirmation.',
                'type'        => 'string',
                'description' => 'Instructions shown to users on the checkout page',
                'category'    => 'payments',
            ],
        ];

        foreach ($settings as $setting) {
            DB::table('app_settings')->updateOrInsert(
                ['key' => $setting['key']],
                array_merge($setting, ['created_at' => $now, 'updated_at' => $now])
            );
        }
    }

    public function down()
    {
        DB::table('app_settings')
            ->whereIn('key', ['instapay_username', 'instapay_phone', 'instapay_instructions'])
            ->delete();
    }
}

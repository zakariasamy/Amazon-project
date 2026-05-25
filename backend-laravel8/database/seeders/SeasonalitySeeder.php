<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SeasonalitySeeder extends Seeder
{
    public function run()
    {
        $now = now();
        $year = date('Y');

        // Amazon US Seasonality (baseline = 1.00)
        $usSeasonality = [
            1 => ['multiplier' => 0.85, 'notes' => 'Post-holiday slump'],
            2 => ['multiplier' => 0.88, 'notes' => 'Winter slow period'],
            3 => ['multiplier' => 0.92, 'notes' => 'Spring starting'],
            4 => ['multiplier' => 0.95, 'notes' => 'Easter/Spring'],
            5 => ['multiplier' => 1.00, 'notes' => 'Mother\'s Day'],
            6 => ['multiplier' => 0.98, 'notes' => 'Father\'s Day / Summer start'],
            7 => ['multiplier' => 1.05, 'notes' => 'Prime Day boost'],
            8 => ['multiplier' => 1.02, 'notes' => 'Back to school'],
            9 => ['multiplier' => 0.95, 'notes' => 'Post summer'],
            10 => ['multiplier' => 1.08, 'notes' => 'Prime Early Access / Halloween prep'],
            11 => ['multiplier' => 1.35, 'notes' => 'Black Friday / Cyber Monday'],
            12 => ['multiplier' => 1.55, 'notes' => 'Christmas peak'],
        ];

        foreach ($usSeasonality as $month => $data) {
            DB::table('seasonality_factors')->insert([
                'marketplace' => 'amazon.com',
                'month' => $month,
                'multiplier' => $data['multiplier'],
                'year' => $year,
                'notes' => $data['notes'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // Amazon Egypt Seasonality (Ramadan/Eid patterns)
        $egSeasonality = [
            1 => ['multiplier' => 0.90, 'notes' => 'Post-holiday'],
            2 => ['multiplier' => 0.92, 'notes' => 'Normal'],
            3 => ['multiplier' => 1.15, 'notes' => 'Ramadan preparation (varies by year)'],
            4 => ['multiplier' => 1.30, 'notes' => 'Ramadan/Eid Al-Fitr peak (varies)'],
            5 => ['multiplier' => 0.95, 'notes' => 'Post-Eid slump'],
            6 => ['multiplier' => 1.10, 'notes' => 'Eid Al-Adha preparation (varies)'],
            7 => ['multiplier' => 0.88, 'notes' => 'Summer slow'],
            8 => ['multiplier' => 0.92, 'notes' => 'Back to school prep'],
            9 => ['multiplier' => 1.00, 'notes' => 'School start'],
            10 => ['multiplier' => 0.95, 'notes' => 'Normal'],
            11 => ['multiplier' => 1.15, 'notes' => 'White Friday (Egypt Black Friday)'],
            12 => ['multiplier' => 1.05, 'notes' => 'Year end'],
        ];

        foreach ($egSeasonality as $month => $data) {
            DB::table('seasonality_factors')->insert([
                'marketplace' => 'amazon.eg',
                'month' => $month,
                'multiplier' => $data['multiplier'],
                'year' => $year,
                'notes' => $data['notes'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $this->command->info('Seasonality factors seeded successfully!');
    }
}

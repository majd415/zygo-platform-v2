<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('settings')->insert([
            'app_name' => 'Taxi Application Syria',
            'logo' => null,
            'price_per_km_usd' => 1.50,
            'price_per_km_syp' => 20000,
            'support_phone' => '+963900000000',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}

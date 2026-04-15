<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceAreaSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('service_areas')->insert([
            [
                'name' => 'Damascus Main Area',
                'latitude' => 33.5138,
                'longitude' => 36.2765,
                'radius_km' => 30.0,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Aleppo Area',
                'latitude' => 36.2021,
                'longitude' => 37.1343,
                'radius_km' => 25.0,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}

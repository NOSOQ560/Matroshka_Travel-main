<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LocationsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('locations')->insert([
            ['name' => 'Airport Cairo', 'type' => 'airport'],
            ['name' => 'Town Alexandria', 'type' => 'town'],
            ['name' => 'Countryside Giza', 'type' => 'countryside'],
            ['name' => 'Airport Dubai', 'type' => 'airport'],
            ['name' => 'Town Cairo', 'type' => 'town'],
            ['name' => 'Countryside Luxor', 'type' => 'countryside'],
        ]);
    }
}

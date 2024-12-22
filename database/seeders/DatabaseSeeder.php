<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $seeders = [
            UserSeeder::class,
            StorySeeder::class,
            ConfigrationSeeder::class,
            CategorySeeder::class,
            ProductSeeder::class,
            CarSeeder::class,
            AirportSeeder::class,
            HotelSeeder::class,
        ];
        foreach ($seeders as $seeder) {
            $this->call($seeder);
            $this->command->outputComponents()->success("{$seeder} run successfully!");
        }
    }
}

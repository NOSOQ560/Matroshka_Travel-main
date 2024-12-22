<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AirportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $total = 20;
        $chunk = (int) ceil($total / 4);
        collect(range(1, $total))
            ->chunk($chunk)
            ->each(function ($chunk) {
                $data = $chunk->map(function () {
                    return [
                        'name' => fake()->text(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                })->toArray();
                DB::table('airports')->insert($data);
            });
    }
}

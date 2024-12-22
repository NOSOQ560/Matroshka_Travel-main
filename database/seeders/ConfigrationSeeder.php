<?php

namespace Database\Seeders;

use App\Enums\ConfigrationTypeEnum;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConfigrationSeeder extends Seeder
{
    public function run(): void
    {
        $total = 20;
        $chunk = (int) ceil($total / 4);
        collect(range(1, $total))
            ->chunk($chunk)
            ->each(function ($chunk) {
                $data = $chunk->map(function () {
                    return [
                        'key' => fake()->text(),
                        'value' => fake()->text(),
                        'type' => fake()->randomElement(array_column(ConfigrationTypeEnum::cases(), 'value')),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                })->toArray();
                DB::table('configrations')->insert($data);
            });
    }
}

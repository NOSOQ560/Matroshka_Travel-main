<?php

namespace Database\Seeders;

use App\Enums\CarTypeEnum;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CarSeeder extends Seeder
{
    public function run(): void
    {
        $timestamps = [
            'created_at' => now(),
            'updated_at' => now(),
        ];
        $data = [
            // business
            [
                'name' => 'Mercedes E-class',
                'brand' => 'Mercedes',
                'type' => CarTypeEnum::business->value,
                'passenger_from' => 1,
                'passenger_to' => 3,
                'package_from' => 1,
                'package_to' => 3,
                'airport_to_town' => 100,
                'hour_in_town' => 25,
            ],
            [
                'name' => 'Mercedes S-class',
                'brand' => 'Mercedes',
                'type' => CarTypeEnum::business->value,
                'passenger_from' => 1,
                'passenger_to' => 3,
                'package_from' => 2,
                'package_to' => 2,
                'airport_to_town' => 130,
                'hour_in_town' => 35,
            ],
            [
                'name' => 'Maybach S222',
                'brand' => 'Mercedes',
                'type' => CarTypeEnum::business->value,
                'passenger_from' => 1,
                'passenger_to' => 3,
                'package_from' => 2,
                'package_to' => 2,
                'airport_to_town' => 160,
                'hour_in_town' => 50,
            ],
            [
                'name' => 'Mercedes V-class',
                'brand' => 'Mercedes',
                'type' => CarTypeEnum::business->value,
                'passenger_from' => 1,
                'passenger_to' => 6,
                'package_from' => 6,
                'package_to' => 6,
                'airport_to_town' => 150,
                'hour_in_town' => 35,
            ],
            [
                'name' => 'Mercedes V-class',
                'brand' => 'Mercedes',
                'type' => CarTypeEnum::business->value,
                'passenger_from' => 1,
                'passenger_to' => 7,
                'package_from' => 7,
                'package_to' => 7,
                'airport_to_town' => 150,
                'hour_in_town' => 35,
            ],
            [
                'name' => 'Mercedes VIP',
                'brand' => 'Mercedes',
                'type' => CarTypeEnum::business->value,
                'passenger_from' => 7,
                'passenger_to' => 10,
                'package_from' => 10,
                'package_to' => 10,
                'airport_to_town' => 170,
                'hour_in_town' => 45,
            ],
            [
                'name' => 'Mini bus',
                'brand' => 'Mercedes',
                'type' => CarTypeEnum::business->value,
                'passenger_from' => 10,
                'passenger_to' => 15,
                'package_from' => 15,
                'package_to' => 15,
                'airport_to_town' => 170,
                'hour_in_town' => 45,
            ],
            [
                'name' => 'Mini bus',
                'brand' => 'Mercedes',
                'type' => CarTypeEnum::business->value,
                'passenger_from' => 10,
                'passenger_to' => 17,
                'package_from' => 15,
                'package_to' => 15,
                'airport_to_town' => 180,
                'hour_in_town' => 45,
            ],
            [
                'name' => 'Mini bus',
                'brand' => 'Mercedes',
                'type' => CarTypeEnum::business->value,
                'passenger_from' => 10,
                'passenger_to' => 20,
                'package_from' => 17,
                'package_to' => 17,
                'airport_to_town' => 180,
                'hour_in_town' => 45,
            ],
            // normal
            [
                'name' => 'Sedan',
                'brand' => null,
                'type' => CarTypeEnum::normal->value,
                'passenger_from' => 1,
                'passenger_to' => 3,
                'package_from' => 2,
                'package_to' => 2,
                'airport_to_town' => 90,
                'hour_in_town' => 20,
            ],
            [
                'name' => 'Minivan',
                'brand' => null,
                'type' => CarTypeEnum::normal->value,
                'passenger_from' => 1,
                'passenger_to' => 6,
                'package_from' => 6,
                'package_to' => 6,
                'airport_to_town' => 100,
                'hour_in_town' => 30,
            ],
            [
                'name' => 'Minivan',
                'brand' => null,
                'type' => CarTypeEnum::normal->value,
                'passenger_from' => 1,
                'passenger_to' => 7,
                'package_from' => 7,
                'package_to' => 7,
                'airport_to_town' => 100,
                'hour_in_town' => 30,
            ],
            [
                'name' => 'Minivan',
                'brand' => null,
                'type' => CarTypeEnum::normal->value,
                'passenger_from' => 5,
                'passenger_to' => 8,
                'package_from' => 8,
                'package_to' => 8,
                'airport_to_town' => 100,
                'hour_in_town' => 30,
            ],
            [
                'name' => 'Mini bus',
                'brand' => null,
                'type' => CarTypeEnum::normal->value,
                'passenger_from' => 5,
                'passenger_to' => 10,
                'package_from' => 10,
                'package_to' => 10,
                'airport_to_town' => 150,
                'hour_in_town' => 40,
            ],
            [
                'name' => 'Mini bus',
                'brand' => null,
                'type' => CarTypeEnum::normal->value,
                'passenger_from' => 10,
                'passenger_to' => 15,
                'package_from' => 14,
                'package_to' => 14,
                'airport_to_town' => 150,
                'hour_in_town' => 40,
            ],
            [
                'name' => 'Mini bus',
                'brand' => null,
                'type' => CarTypeEnum::normal->value,
                'passenger_from' => 15,
                'passenger_to' => 20,
                'package_from' => 18,
                'package_to' => 18,
                'airport_to_town' => 170,
                'hour_in_town' => 45,
            ],
        ];
        $chunkSize = (int) ceil(count($data) / 4);

        $data = collect($data)->map(function ($record) use ($timestamps) {
            return array_merge($record, $timestamps);
        });

        collect($data)->chunk($chunkSize)->each(function ($chunk) {
            DB::table('cars')->insert($chunk->toArray());
        });
    }
}

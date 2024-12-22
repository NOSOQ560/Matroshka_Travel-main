<?php

namespace Database\Seeders;

use App\Enums\GenderTypeEnum;
use App\Enums\UserTypeEnum;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
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
                        'name' => fake()->text(),
                        'type' => fake()->randomElement(array_column(UserTypeEnum::cases(), 'value')),
                        'email' => fake()->safeEmail(),
                        'email_verified_at' => now(),
                        'password' => bcrypt('123456789'),
                        'phone' => fake()->phoneNumber(),
                        'country' => fake()->countryCode(),
                        'gender' => fake()->randomElement(array_column(GenderTypeEnum::cases(), 'value')),
                        'website' => fake()->url(),
                        'social_media' => fake()->url(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                })->toArray();
                DB::table('users')->insert($data);
            });
    }
}

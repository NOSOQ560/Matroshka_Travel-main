<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
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
                        'category_id' => Category::inRandomOrder()->value('id'),
                        'name' => fake()->text(),
                        'description' => fake()->text(),
                        'price' => fake()->randomFloat(1, 20, 30),
                        'stock' => fake()->randomDigitNotNull(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                })->toArray();
                DB::table('products')->insert($data);

                $insertedModelIds = DB::table('products')
                    ->latest('id')
                    ->take(count($data))
                    ->pluck('id')
                    ->reverse();
                foreach ($insertedModelIds as $modelId) {
                    $result = Product::find($modelId);
                    $result->addMediaFromUrl('https://avatars.githubusercontent.com/u/97165289')
                        ->toMediaCollection('product');
                }
            });
    }
}

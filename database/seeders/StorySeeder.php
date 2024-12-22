<?php

namespace Database\Seeders;

use App\Models\Story;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StorySeeder extends Seeder
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
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                })->toArray();
                DB::table('stories')->insert($data);

                $insertedModelIds = DB::table('stories')
                    ->latest('id')
                    ->take(count($data))
                    ->pluck('id')
                    ->reverse();
                foreach ($insertedModelIds as $modelId) {
                    $result = Story::find($modelId);
                    $result->addMediaFromUrl('https://avatars.githubusercontent.com/u/97165289')->toMediaCollection('story');
                }
            });
    }
}

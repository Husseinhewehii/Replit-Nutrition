<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Food;
use App\Models\Portion;
use Carbon\Carbon;

class CypressTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create test user
        $user = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );

        // Create test foods
        $foods = [
            [
                'name' => 'Apple',
                'slug' => 'apple',
                'kcal_per_100g' => 52,
                'protein_per_100g' => 0.3,
                'carbs_per_100g' => 14,
                'fat_per_100g' => 0.2,
                'is_global' => true,
            ],
            [
                'name' => 'Banana',
                'slug' => 'banana',
                'kcal_per_100g' => 89,
                'protein_per_100g' => 1.1,
                'carbs_per_100g' => 23,
                'fat_per_100g' => 0.3,
                'is_global' => true,
            ],
            [
                'name' => 'Chicken Breast',
                'slug' => 'chicken-breast',
                'kcal_per_100g' => 165,
                'protein_per_100g' => 31,
                'carbs_per_100g' => 0,
                'fat_per_100g' => 3.6,
                'is_global' => true,
            ],
        ];

        foreach ($foods as $foodData) {
            Food::firstOrCreate(
                ['slug' => $foodData['slug']],
                $foodData
            );
        }

        // Get food models
        $apple = Food::where('name', 'Apple')->first();
        $banana = Food::where('name', 'Banana')->first();
        $chicken = Food::where('name', 'Chicken Breast')->first();

        // Create portions for today
        $today = Carbon::today();
        Portion::firstOrCreate([
            'user_id' => $user->id,
            'food_id' => $apple->id,
            'grams' => 150,
            'consumed_at' => $today,
        ]);

        Portion::firstOrCreate([
            'user_id' => $user->id,
            'food_id' => $banana->id,
            'grams' => 120,
            'consumed_at' => $today,
        ]);

        // Create portions for yesterday
        $yesterday = Carbon::yesterday();
        Portion::firstOrCreate([
            'user_id' => $user->id,
            'food_id' => $chicken->id,
            'grams' => 200,
            'consumed_at' => $yesterday,
        ]);

        Portion::firstOrCreate([
            'user_id' => $user->id,
            'food_id' => $apple->id,
            'grams' => 100,
            'consumed_at' => $yesterday,
        ]);

        // Create portions for day before yesterday
        $dayBeforeYesterday = Carbon::yesterday()->subDay();
        Portion::firstOrCreate([
            'user_id' => $user->id,
            'food_id' => $banana->id,
            'grams' => 80,
            'consumed_at' => $dayBeforeYesterday,
        ]);
    }
}



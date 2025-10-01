<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Food>
 */
class FoodFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(2, true),
            'slug' => $this->faker->unique()->slug(2),
            'kcal_per_100g' => $this->faker->numberBetween(50, 500),
            'protein_per_100g' => $this->faker->randomFloat(1, 0, 50),
            'carbs_per_100g' => $this->faker->randomFloat(1, 0, 100),
            'fat_per_100g' => $this->faker->randomFloat(1, 0, 50),
            'is_global' => false,
            'user_id' => null,
        ];
    }

    public function global(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_global' => true,
            'user_id' => null,
        ]);
    }
}

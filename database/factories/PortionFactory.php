<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Portion>
 */
class PortionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'food_id' => \App\Models\Food::factory(),
            'grams' => $this->faker->numberBetween(50, 300),
            'consumed_at' => $this->faker->date(),
        ];
    }
}

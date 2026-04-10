<?php

namespace Database\Factories;

use App\Models\CurrentState;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CurrentState>
 */
class CurrentStateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->word(),
            'description' => fake()->optional()->sentence(),
            'slug' => fake()->unique()->slug(),
        ];
    }
}

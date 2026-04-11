<?php

namespace Database\Factories;

use App\Enums\Priority;
use App\Models\CurrentState;
use App\Models\Ticket;
use App\Models\Type;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ticket>
 */
class TicketFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'subject' => fake()->sentence(),
            'description' => fake()->optional()->sentence(),
            'type_id' => Type::factory(),
            'current_state_id' => CurrentState::factory(),
            'priority' => fake()->randomElement(Priority::cases()),
            'ticket_number' => 'TKT-'.fake()->unique()->numberBetween(100000, 999999),
            'requested_by_id' => User::factory(),
            'assigned_to_id' => User::factory(),
            'created_by_id' => User::factory(),
            'updated_by_id' => User::factory(),
            'deleted_by_id' => null,
            'deleted_at' => null,
            'resolved_at' => null,
        ];
    }
}

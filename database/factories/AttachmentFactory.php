<?php

namespace Database\Factories;

use App\Models\Attachment;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Attachment>
 */
class AttachmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $filename = fake()->lexify('????????').'.pdf';

        return [
            'ticket_id' => Ticket::factory(),
            'filename' => $filename,
            'path' => 'attachments/'.fake()->uuid().'/'.$filename,
            'mime_type' => 'application/pdf',
            'size' => fake()->numberBetween(100, 1_000_000),
        ];
    }
}

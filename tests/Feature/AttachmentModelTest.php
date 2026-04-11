<?php

namespace Tests\Feature;

use App\Models\Attachment;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttachmentModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_fillable_columns_are_defined_via_attribute(): void
    {
        $this->assertSame(
            ['ticket_id', 'filename', 'path', 'mime_type', 'size'],
            (new Attachment)->getFillable()
        );
    }

    public function test_factory_creates_attachment_for_ticket(): void
    {
        $attachment = Attachment::factory()->create();

        $this->assertNotEmpty($attachment->id);
        $this->assertInstanceOf(Ticket::class, $attachment->ticket);
        $this->assertIsString($attachment->filename);
        $this->assertIsString($attachment->path);
        $this->assertIsString($attachment->mime_type);
        $this->assertIsInt($attachment->size);
    }

    public function test_ticket_has_many_attachments(): void
    {
        $ticket = Ticket::factory()->create();
        Attachment::factory()->count(2)->for($ticket)->create();

        $this->assertCount(2, $ticket->fresh()->attachments);
    }
}

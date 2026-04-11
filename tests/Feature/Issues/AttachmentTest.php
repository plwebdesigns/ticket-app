<?php

namespace Tests\Feature\Issues;

use App\Enums\Priority;
use App\Models\Attachment;
use App\Models\CurrentState;
use App\Models\Ticket;
use App\Models\Type;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AttachmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_download_an_attachment(): void
    {
        $attachment = Attachment::factory()->create();

        $this->get(route('attachments.show', $attachment))
            ->assertRedirect(route('login'));
    }

    public function test_guests_cannot_delete_an_attachment(): void
    {
        $attachment = Attachment::factory()->create();

        $this->delete(route('attachments.destroy', $attachment))
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_download_an_attachment(): void
    {
        Storage::fake();

        $attachment = Attachment::factory()->create([
            'path' => 'attachments/test/report.pdf',
            'filename' => 'report.pdf',
        ]);

        Storage::put($attachment->path, 'file-contents');

        $this->actingAs(User::factory()->create())
            ->get(route('attachments.show', $attachment))
            ->assertOk()
            ->assertDownload('report.pdf');
    }

    public function test_authenticated_user_can_view_a_pdf_attachment_inline(): void
    {
        Storage::fake();

        $attachment = Attachment::factory()->create([
            'path' => 'attachments/test/report.pdf',
            'filename' => 'report.pdf',
            'mime_type' => 'application/pdf',
        ]);

        Storage::put($attachment->path, 'file-contents');

        $response = $this->actingAs(User::factory()->create())
            ->get(route('attachments.show', ['attachment' => $attachment, 'inline' => 1]));

        $response->assertOk();

        $this->assertStringStartsWith('inline;', (string) $response->headers->get('content-disposition'));
        $this->assertStringStartsWith('application/pdf', (string) $response->headers->get('content-type'));
    }

    public function test_non_pdf_attachment_with_inline_query_still_downloads(): void
    {
        Storage::fake();

        $attachment = Attachment::factory()->create([
            'path' => 'attachments/test/screenshot.png',
            'filename' => 'screenshot.png',
            'mime_type' => 'image/png',
        ]);

        Storage::put($attachment->path, 'file-contents');

        $this->actingAs(User::factory()->create())
            ->get(route('attachments.show', ['attachment' => $attachment, 'inline' => 1]))
            ->assertOk()
            ->assertDownload('screenshot.png');
    }

    public function test_authenticated_user_can_delete_an_attachment(): void
    {
        Storage::fake();

        $attachment = Attachment::factory()->create([
            'path' => 'attachments/test/report.pdf',
        ]);

        Storage::put($attachment->path, 'file-contents');

        $this->actingAs(User::factory()->create())
            ->delete(route('attachments.destroy', $attachment))
            ->assertRedirect();

        $this->assertModelMissing($attachment);
        Storage::assertMissing($attachment->path);
    }

    public function test_creating_a_ticket_with_attachments_stores_files(): void
    {
        Storage::fake();

        $user = User::factory()->create();
        $type = Type::factory()->create();
        $state = CurrentState::factory()->create();

        $this->actingAs($user)
            ->post(route('issues.store'), [
                'subject' => 'With attachments',
                'description' => null,
                'type_id' => $type->id,
                'current_state_id' => $state->id,
                'priority' => Priority::NORMAL->value,
                'requested_by_id' => null,
                'assigned_to_id' => null,
                'attachments' => [
                    UploadedFile::fake()->create('design.pdf', 512),
                    UploadedFile::fake()->image('screenshot.png', 100, 100),
                ],
            ])
            ->assertRedirect(route('issues.index'));

        $ticket = Ticket::query()->first();
        $this->assertNotNull($ticket);
        $this->assertCount(2, $ticket->attachments);

        $filenames = $ticket->attachments->pluck('filename')->sort()->values()->all();
        $this->assertSame(['design.pdf', 'screenshot.png'], $filenames);

        foreach ($ticket->attachments as $attachment) {
            Storage::assertExists($attachment->path);
            $this->assertStringStartsWith("attachments/{$ticket->id}/", $attachment->path);
        }
    }

    public function test_updating_a_ticket_with_new_attachments_stores_files(): void
    {
        Storage::fake();

        $user = User::factory()->create();
        $type = Type::factory()->create();
        $state = CurrentState::factory()->create();

        $ticket = Ticket::factory()->create([
            'type_id' => $type->id,
            'current_state_id' => $state->id,
            'created_by_id' => $user->id,
            'updated_by_id' => $user->id,
            'deleted_at' => null,
            'deleted_by_id' => null,
        ]);

        $this->actingAs($user)
            ->from(route('issues.show', $ticket))
            ->put(route('issues.update', $ticket), [
                'subject' => $ticket->subject,
                'description' => $ticket->description,
                'type_id' => $type->id,
                'current_state_id' => $state->id,
                'priority' => $ticket->priority->value,
                'requested_by_id' => null,
                'assigned_to_id' => null,
                'attachments' => [
                    UploadedFile::fake()->create('notes.txt', 128),
                ],
            ])
            ->assertRedirect(route('issues.show', $ticket));

        $this->assertCount(1, $ticket->attachments);
        $attachment = $ticket->attachments->first();
        $this->assertSame('notes.txt', $attachment->filename);
        Storage::assertExists($attachment->path);
    }

    public function test_show_page_includes_attachments(): void
    {
        $user = User::factory()->create();
        $ticket = Ticket::factory()->create([
            'deleted_at' => null,
            'deleted_by_id' => null,
        ]);

        Attachment::factory()->count(2)->create(['ticket_id' => $ticket->id]);

        $this->actingAs($user)
            ->get(route('issues.show', $ticket))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('issues/Show', false)
                ->has('ticket.attachments', 2));
    }

    public function test_attachment_file_too_large_is_rejected(): void
    {
        Storage::fake();

        $user = User::factory()->create();
        $type = Type::factory()->create();
        $state = CurrentState::factory()->create();

        $this->actingAs($user)
            ->post(route('issues.store'), [
                'subject' => 'Too big',
                'description' => null,
                'type_id' => $type->id,
                'current_state_id' => $state->id,
                'priority' => Priority::NORMAL->value,
                'requested_by_id' => null,
                'assigned_to_id' => null,
                'attachments' => [
                    UploadedFile::fake()->create('huge.zip', 11_000),
                ],
            ])
            ->assertSessionHasErrors('attachments.0');

        $this->assertDatabaseCount('attachments', 0);
    }

    public function test_non_file_attachment_value_is_rejected(): void
    {
        $user = User::factory()->create();
        $type = Type::factory()->create();
        $state = CurrentState::factory()->create();

        $this->actingAs($user)
            ->post(route('issues.store'), [
                'subject' => 'Bad input',
                'description' => null,
                'type_id' => $type->id,
                'current_state_id' => $state->id,
                'priority' => Priority::NORMAL->value,
                'requested_by_id' => null,
                'assigned_to_id' => null,
                'attachments' => ['not-a-file'],
            ])
            ->assertSessionHasErrors('attachments.0');

        $this->assertDatabaseCount('attachments', 0);
    }

    public function test_creating_a_ticket_with_a_single_file_attachment_succeeds(): void
    {
        Storage::fake();

        $user = User::factory()->create();
        $type = Type::factory()->create();
        $state = CurrentState::factory()->create();

        $this->actingAs($user)
            ->post(route('issues.store'), [
                'subject' => 'Single file',
                'description' => null,
                'type_id' => $type->id,
                'current_state_id' => $state->id,
                'priority' => Priority::NORMAL->value,
                'requested_by_id' => null,
                'assigned_to_id' => null,
                'attachments' => UploadedFile::fake()->create('solo.pdf', 256),
            ])
            ->assertRedirect(route('issues.index'));

        $ticket = Ticket::query()->first();
        $this->assertNotNull($ticket);
        $this->assertCount(1, $ticket->attachments);
        $this->assertSame('solo.pdf', $ticket->attachments->first()->filename);
        Storage::assertExists($ticket->attachments->first()->path);
    }

    public function test_creating_a_ticket_without_attachments_succeeds(): void
    {
        $user = User::factory()->create();
        $type = Type::factory()->create();
        $state = CurrentState::factory()->create();

        $this->actingAs($user)
            ->post(route('issues.store'), [
                'subject' => 'No files',
                'description' => null,
                'type_id' => $type->id,
                'current_state_id' => $state->id,
                'priority' => Priority::LOW->value,
                'requested_by_id' => null,
                'assigned_to_id' => null,
            ])
            ->assertRedirect(route('issues.index'));

        $ticket = Ticket::query()->first();
        $this->assertNotNull($ticket);
        $this->assertCount(0, $ticket->attachments);
    }
}

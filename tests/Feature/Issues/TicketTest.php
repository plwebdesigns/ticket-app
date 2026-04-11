<?php

namespace Tests\Feature\Issues;

use App\Enums\Priority;
use App\Models\CurrentState;
use App\Models\Ticket;
use App\Models\Type;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class TicketTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_access_issues_index(): void
    {
        $this->get(route('issues.index'))->assertRedirect(route('login'));
    }

    public function test_guests_cannot_access_issues_show(): void
    {
        $ticket = Ticket::factory()->create([
            'deleted_at' => null,
            'deleted_by_id' => null,
        ]);

        $this->get(route('issues.show', $ticket))->assertRedirect(route('login'));
    }

    public function test_verified_users_can_view_issues_index(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('issues.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('issues/Index', false));
    }

    public function test_verified_user_can_view_a_ticket(): void
    {
        $user = User::factory()->create();
        $ticket = Ticket::factory()->create([
            'subject' => 'View me',
            'deleted_at' => null,
            'deleted_by_id' => null,
        ]);

        $this->actingAs($user)
            ->get(route('issues.show', $ticket))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('issues/Show', false)
                ->where('ticket.id', $ticket->id)
                ->where('ticket.subject', 'View me'));
    }

    public function test_verified_user_can_create_a_ticket(): void
    {
        $user = User::factory()->create();
        $type = Type::factory()->create();
        $state = CurrentState::factory()->create();

        $this->actingAs($user)
            ->post(route('issues.store'), [
                'subject' => 'Broken login',
                'description' => 'Cannot sign in.',
                'type_id' => $type->id,
                'current_state_id' => $state->id,
                'priority' => Priority::HIGH->value,
                'requested_by_id' => $user->id,
                'assigned_to_id' => null,
            ])
            ->assertRedirect(route('issues.index'));

        $this->assertDatabaseHas('tickets', [
            'subject' => 'Broken login',
            'type_id' => $type->id,
            'current_state_id' => $state->id,
            'priority' => Priority::HIGH->value,
            'requested_by_id' => $user->id,
            'assigned_to_id' => null,
            'created_by_id' => $user->id,
            'updated_by_id' => $user->id,
        ]);

        $ticket = Ticket::query()->first();
        $this->assertNotNull($ticket);
        $this->assertStringStartsWith('TKT-', $ticket->ticket_number);
    }

    public function test_verified_user_can_update_a_ticket(): void
    {
        $user = User::factory()->create();
        $type = Type::factory()->create();
        $state = CurrentState::factory()->create();
        $otherState = CurrentState::factory()->create();

        $ticket = Ticket::factory()->create([
            'subject' => 'Old subject',
            'type_id' => $type->id,
            'current_state_id' => $state->id,
            'priority' => Priority::LOW,
            'created_by_id' => $user->id,
            'updated_by_id' => $user->id,
            'deleted_at' => null,
            'deleted_by_id' => null,
        ]);

        $this->actingAs($user)
            ->from(route('issues.index'))
            ->put(route('issues.update', $ticket), [
                'subject' => 'New subject',
                'description' => 'Updated notes',
                'type_id' => $type->id,
                'current_state_id' => $otherState->id,
                'priority' => Priority::CRITICAL->value,
                'requested_by_id' => null,
                'assigned_to_id' => $user->id,
            ])
            ->assertRedirect(route('issues.index'));

        $ticket->refresh();

        $this->assertSame('New subject', $ticket->subject);
        $this->assertSame('Updated notes', $ticket->description);
        $this->assertSame($otherState->id, $ticket->current_state_id);
        $this->assertSame(Priority::CRITICAL, $ticket->priority);
        $this->assertSame($user->id, $ticket->assigned_to_id);
        $this->assertSame($user->id, $ticket->updated_by_id);
    }

    public function test_verified_user_redirects_to_show_when_updating_from_ticket_page(): void
    {
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
                'subject' => 'From show page',
                'description' => null,
                'type_id' => $type->id,
                'current_state_id' => $state->id,
                'priority' => Priority::NORMAL->value,
                'requested_by_id' => null,
                'assigned_to_id' => null,
            ])
            ->assertRedirect(route('issues.show', $ticket));

        $this->assertSame('From show page', $ticket->fresh()->subject);
    }

    public function test_verified_user_can_soft_delete_a_ticket(): void
    {
        $user = User::factory()->create();
        $ticket = Ticket::factory()->create([
            'created_by_id' => $user->id,
            'updated_by_id' => $user->id,
            'deleted_at' => null,
            'deleted_by_id' => null,
        ]);

        $this->actingAs($user)
            ->delete(route('issues.destroy', $ticket))
            ->assertRedirect(route('issues.index'));

        $this->assertSoftDeleted($ticket);
        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'deleted_by_id' => $user->id,
        ]);
    }
}

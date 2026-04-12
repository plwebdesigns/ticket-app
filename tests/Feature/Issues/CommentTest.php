<?php

namespace Tests\Feature\Issues;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_post_a_comment(): void
    {
        $ticket = Ticket::factory()->create([
            'deleted_at' => null,
            'deleted_by_id' => null,
        ]);

        $this->post(route('comments.store', $ticket), [
            'comment' => 'Hello',
        ])->assertRedirect(route('login'));

        $this->assertDatabaseCount('comments', 0);
    }

    public function test_verified_user_can_post_a_comment(): void
    {
        $user = User::factory()->create();
        $ticket = Ticket::factory()->create([
            'deleted_at' => null,
            'deleted_by_id' => null,
        ]);

        $this->actingAs($user)
            ->from(route('issues.show', $ticket))
            ->post(route('comments.store', $ticket), [
                'comment' => 'First thread reply',
            ])
            ->assertRedirect(route('issues.show', $ticket));

        $this->assertDatabaseHas('comments', [
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'comment' => 'First thread reply',
        ]);

        $this->actingAs($user)
            ->get(route('issues.show', $ticket))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('issues/Show', false)
                ->where('ticket.comments.0.comment', 'First thread reply')
                ->where('ticket.comments.0.user.name', $user->name));
    }

    public function test_comment_body_is_required(): void
    {
        $user = User::factory()->create();
        $ticket = Ticket::factory()->create([
            'deleted_at' => null,
            'deleted_by_id' => null,
        ]);

        $this->actingAs($user)
            ->from(route('issues.show', $ticket))
            ->post(route('comments.store', $ticket), [
                'comment' => '',
            ])
            ->assertRedirect(route('issues.show', $ticket))
            ->assertSessionHasErrors('comment');

        $this->assertDatabaseCount('comments', 0);
    }
}

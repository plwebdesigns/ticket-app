<?php

namespace Tests\Feature\Admin;

use App\Enums\Priority;
use App\Models\CurrentState;
use App\Models\Role;
use App\Models\Ticket;
use App\Models\Type;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminSettingsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array{0: Role, 1: Role, 2: User, 3: User}
     */
    private function seedRolesAndUsers(): array
    {
        $adminRole = Role::factory()->create(['name' => 'admin']);
        $userRole = Role::factory()->create(['name' => 'user']);
        $admin = User::factory()->create(['role_id' => $adminRole->id]);
        $regular = User::factory()->create(['role_id' => $userRole->id]);

        return [$adminRole, $userRole, $admin, $regular];
    }

    public function test_guest_cannot_access_admin_settings(): void
    {
        $this->get(route('admin.index'))->assertRedirect();
    }

    public function test_non_admin_cannot_access_admin_settings(): void
    {
        [, , , $regular] = $this->seedRolesAndUsers();

        $this->actingAs($regular)
            ->get(route('admin.index'))
            ->assertForbidden();
    }

    public function test_non_admin_cannot_mutate_admin_resources(): void
    {
        [, , , $regular] = $this->seedRolesAndUsers();
        $role = Role::factory()->create(['name' => 'manager']);

        $this->actingAs($regular)
            ->post(route('admin.roles.store'), [
                'name' => 'extra',
                'description' => null,
            ])
            ->assertForbidden();

        $this->actingAs($regular)
            ->put(route('admin.roles.update', $role), [
                'name' => 'renamed',
                'description' => null,
            ])
            ->assertForbidden();

        $this->actingAs($regular)
            ->delete(route('admin.roles.destroy', $role))
            ->assertForbidden();
    }

    public function test_admin_can_view_admin_settings_index(): void
    {
        [, , $admin] = $this->seedRolesAndUsers();

        $this->actingAs($admin)
            ->get(route('admin.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('settings/admin/Index')
                ->has('roles')
                ->has('types')
                ->has('currentStates')
                ->has('users'));
    }

    public function test_admin_can_create_and_update_role(): void
    {
        [, , $admin] = $this->seedRolesAndUsers();
        $role = Role::factory()->create(['name' => 'manager', 'description' => 'Mgr']);

        $this->actingAs($admin)
            ->post(route('admin.roles.store'), [
                'name' => 'support',
                'description' => 'Support team',
            ])
            ->assertRedirect(route('admin.index'));

        $this->assertDatabaseHas('roles', [
            'name' => 'support',
            'description' => 'Support team',
        ]);

        $this->actingAs($admin)
            ->put(route('admin.roles.update', $role), [
                'name' => 'manager-updated',
                'description' => 'Updated',
            ])
            ->assertRedirect(route('admin.index'));

        $this->assertDatabaseHas('roles', [
            'id' => $role->id,
            'name' => 'manager-updated',
            'description' => 'Updated',
        ]);
    }

    public function test_admin_cannot_delete_role_assigned_to_users(): void
    {
        [$adminRole, , $admin] = $this->seedRolesAndUsers();

        $this->actingAs($admin)
            ->from(route('admin.index'))
            ->delete(route('admin.roles.destroy', $adminRole))
            ->assertRedirect(route('admin.index'));

        $this->assertDatabaseHas('roles', ['id' => $adminRole->id]);
    }

    public function test_admin_can_delete_role_without_users(): void
    {
        [, , $admin] = $this->seedRolesAndUsers();
        $role = Role::factory()->create(['name' => 'temp-role']);

        $this->actingAs($admin)
            ->delete(route('admin.roles.destroy', $role))
            ->assertRedirect(route('admin.index'));

        $this->assertDatabaseMissing('roles', ['id' => $role->id]);
    }

    public function test_admin_can_manage_types(): void
    {
        [, , $admin] = $this->seedRolesAndUsers();

        $this->actingAs($admin)
            ->post(route('admin.types.store'), [
                'name' => 'Bug Report',
                'description' => 'Bugs',
                'slug' => '',
            ])
            ->assertRedirect(route('admin.index'));

        $this->assertDatabaseHas('types', [
            'name' => 'Bug Report',
            'slug' => 'bug-report',
        ]);

        $type = Type::query()->where('slug', 'bug-report')->firstOrFail();

        $this->actingAs($admin)
            ->put(route('admin.types.update', $type), [
                'name' => 'Bug Report',
                'description' => 'Software bugs',
                'slug' => 'bug-report',
            ])
            ->assertRedirect(route('admin.index'));
    }

    public function test_admin_cannot_delete_type_used_by_ticket(): void
    {
        [, , $admin] = $this->seedRolesAndUsers();
        $type = Type::factory()->create();
        $state = CurrentState::factory()->create();

        Ticket::factory()->create([
            'type_id' => $type->id,
            'current_state_id' => $state->id,
            'priority' => Priority::NORMAL,
            'ticket_number' => 'TKT-900001',
            'deleted_at' => null,
        ]);

        $this->actingAs($admin)
            ->from(route('admin.index'))
            ->delete(route('admin.types.destroy', $type))
            ->assertRedirect(route('admin.index'));

        $this->assertDatabaseHas('types', ['id' => $type->id]);
    }

    public function test_admin_can_manage_current_states(): void
    {
        [, , $admin] = $this->seedRolesAndUsers();

        $this->actingAs($admin)
            ->post(route('admin.current-states.store'), [
                'name' => 'In Review',
                'description' => null,
                'slug' => '',
            ])
            ->assertRedirect(route('admin.index'));

        $this->assertDatabaseHas('current_states', [
            'name' => 'In Review',
            'slug' => 'in-review',
        ]);
    }

    public function test_admin_cannot_delete_current_state_used_by_ticket(): void
    {
        [, , $admin] = $this->seedRolesAndUsers();
        $type = Type::factory()->create();
        $state = CurrentState::factory()->create();

        Ticket::factory()->create([
            'type_id' => $type->id,
            'current_state_id' => $state->id,
            'priority' => Priority::NORMAL,
            'ticket_number' => 'TKT-900002',
            'deleted_at' => null,
        ]);

        $this->actingAs($admin)
            ->from(route('admin.index'))
            ->delete(route('admin.current-states.destroy', $state))
            ->assertRedirect(route('admin.index'));

        $this->assertDatabaseHas('current_states', ['id' => $state->id]);
    }

    public function test_admin_can_create_user(): void
    {
        [, $userRole, $admin] = $this->seedRolesAndUsers();

        $this->actingAs($admin)
            ->post(route('admin.users.store'), [
                'name' => 'New Person',
                'email' => 'newperson@example.com',
                'role_id' => $userRole->id,
                'password' => 'Password123!',
                'password_confirmation' => 'Password123!',
            ])
            ->assertRedirect(route('admin.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'newperson@example.com',
            'role_id' => $userRole->id,
        ]);
    }

    public function test_admin_cannot_delete_self(): void
    {
        [, , $admin] = $this->seedRolesAndUsers();

        $this->actingAs($admin)
            ->from(route('admin.index'))
            ->delete(route('admin.users.destroy', $admin))
            ->assertRedirect(route('admin.index'));

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_admin_can_delete_another_admin_when_not_last(): void
    {
        [$adminRole, , $admin] = $this->seedRolesAndUsers();
        $secondAdmin = User::factory()->create(['role_id' => $adminRole->id]);

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $secondAdmin))
            ->assertRedirect(route('admin.index'));

        $this->assertDatabaseMissing('users', ['id' => $secondAdmin->id]);
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_admin_cannot_demote_last_administrator(): void
    {
        [$adminRole, $userRole, $admin] = $this->seedRolesAndUsers();

        $this->actingAs($admin)
            ->from(route('admin.index'))
            ->put(route('admin.users.update', $admin), [
                'name' => $admin->name,
                'email' => $admin->email,
                'role_id' => $userRole->id,
                'password' => '',
                'password_confirmation' => '',
            ])
            ->assertSessionHasErrors('role_id');
    }

    public function test_admin_can_demote_when_another_admin_exists(): void
    {
        [$adminRole, $userRole, $admin] = $this->seedRolesAndUsers();
        $otherAdmin = User::factory()->create(['role_id' => $adminRole->id]);

        $this->actingAs($otherAdmin)
            ->put(route('admin.users.update', $admin), [
                'name' => $admin->name,
                'email' => $admin->email,
                'role_id' => $userRole->id,
                'password' => '',
                'password_confirmation' => '',
            ])
            ->assertRedirect(route('admin.index'));

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'role_id' => $userRole->id,
        ]);
    }
}

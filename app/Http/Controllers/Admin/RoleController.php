<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRoleRequest;
use App\Http\Requests\Admin\UpdateRoleRequest;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class RoleController extends Controller
{
    public function store(StoreRoleRequest $request): RedirectResponse
    {
        Role::query()->create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Role created.')]);

        return to_route('admin.index');
    }

    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        $role->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Role updated.')]);

        return to_route('admin.index');
    }

    public function destroy(Role $role): RedirectResponse
    {
        if ($role->users()->exists()) {
            Inertia::flash('toast', ['type' => 'error', 'message' => __('Cannot delete a role that is assigned to users.')]);

            return to_route('admin.index');
        }

        $role->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Role deleted.')]);

        return to_route('admin.index');
    }
}

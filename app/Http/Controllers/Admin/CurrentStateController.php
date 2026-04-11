<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCurrentStateRequest;
use App\Http\Requests\Admin\UpdateCurrentStateRequest;
use App\Models\CurrentState;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class CurrentStateController extends Controller
{
    public function store(StoreCurrentStateRequest $request): RedirectResponse
    {
        CurrentState::query()->create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Current state created.')]);

        return to_route('admin.index');
    }

    public function update(UpdateCurrentStateRequest $request, CurrentState $current_state): RedirectResponse
    {
        $current_state->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Current state updated.')]);

        return to_route('admin.index');
    }

    public function destroy(CurrentState $current_state): RedirectResponse
    {
        if ($current_state->tickets()->exists()) {
            Inertia::flash('toast', ['type' => 'error', 'message' => __('Cannot delete a current state that is used by tickets.')]);

            return to_route('admin.index');
        }

        $current_state->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Current state deleted.')]);

        return to_route('admin.index');
    }
}

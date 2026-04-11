<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTypeRequest;
use App\Http\Requests\Admin\UpdateTypeRequest;
use App\Models\Type;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class TypeController extends Controller
{
    public function store(StoreTypeRequest $request): RedirectResponse
    {
        Type::query()->create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Type created.')]);

        return to_route('admin.index');
    }

    public function update(UpdateTypeRequest $request, Type $type): RedirectResponse
    {
        $type->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Type updated.')]);

        return to_route('admin.index');
    }

    public function destroy(Type $type): RedirectResponse
    {
        if ($type->tickets()->exists()) {
            Inertia::flash('toast', ['type' => 'error', 'message' => __('Cannot delete a type that is used by tickets.')]);

            return to_route('admin.index');
        }

        $type->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Type deleted.')]);

        return to_route('admin.index');
    }
}

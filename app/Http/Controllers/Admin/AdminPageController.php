<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CurrentState;
use App\Models\Role;
use App\Models\Type;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class AdminPageController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('settings/admin/Index', [
            'roles' => Role::query()->orderBy('name')->get(),
            'types' => Type::query()->orderBy('name')->get(),
            'currentStates' => CurrentState::query()->orderBy('name')->get(),
            'users' => User::query()->with('role')->orderBy('name')->get(),
        ]);
    }
}

<?php

use App\Http\Controllers\Admin\AdminPageController;
use App\Http\Controllers\Admin\CurrentStateController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\TypeController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Settings\SecurityController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', '/settings/profile');

    Route::get('settings/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('settings/profile', [ProfileController::class, 'update'])->name('profile.update');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::delete('settings/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('settings/security', [SecurityController::class, 'edit'])->name('security.edit');

    Route::put('settings/password', [SecurityController::class, 'update'])
        ->middleware('throttle:6,1')
        ->name('user-password.update');

    Route::inertia('settings/appearance', 'settings/Appearance')->name('appearance.edit');
});

Route::middleware(['auth', 'verified', 'admin'])->group(function (): void {
    Route::get('settings/admin', [AdminPageController::class, 'index'])->name('admin.index');

    Route::post('settings/admin/roles', [RoleController::class, 'store'])->name('admin.roles.store');
    Route::put('settings/admin/roles/{role}', [RoleController::class, 'update'])->name('admin.roles.update');
    Route::delete('settings/admin/roles/{role}', [RoleController::class, 'destroy'])->name('admin.roles.destroy');

    Route::post('settings/admin/types', [TypeController::class, 'store'])->name('admin.types.store');
    Route::put('settings/admin/types/{type}', [TypeController::class, 'update'])->name('admin.types.update');
    Route::delete('settings/admin/types/{type}', [TypeController::class, 'destroy'])->name('admin.types.destroy');

    Route::post('settings/admin/current-states', [CurrentStateController::class, 'store'])->name('admin.current-states.store');
    Route::put('settings/admin/current-states/{current_state}', [CurrentStateController::class, 'update'])->name('admin.current-states.update');
    Route::delete('settings/admin/current-states/{current_state}', [CurrentStateController::class, 'destroy'])->name('admin.current-states.destroy');

    Route::post('settings/admin/users', [UserController::class, 'store'])->name('admin.users.store');
    Route::put('settings/admin/users/{user}', [UserController::class, 'update'])->name('admin.users.update');
    Route::delete('settings/admin/users/{user}', [UserController::class, 'destroy'])->name('admin.users.destroy');
});

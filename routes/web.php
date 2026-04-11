<?php

use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');

    Route::resource('issues', TicketController::class)->only(['index', 'show', 'store', 'update', 'destroy'])->parameters([
        'issues' => 'ticket',
    ]);
});

require __DIR__.'/settings.php';

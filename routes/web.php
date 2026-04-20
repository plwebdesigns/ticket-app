<?php

use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');

    Route::resource('issues', TicketController::class)->only(['index', 'show', 'store', 'update', 'destroy'])->parameters([
        'issues' => 'ticket',
    ]);

    Route::get('attachments/{attachment}', [AttachmentController::class, 'show'])->name('attachments.show');
    Route::delete('attachments/{attachment}', [AttachmentController::class, 'destroy'])->name('attachments.destroy');

    Route::post('issues/{ticket}/comments', [CommentController::class, 'store'])->name('comments.store');
});

require __DIR__.'/settings.php';

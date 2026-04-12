<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CommentController extends Controller
{
    public function store(Request $request, Ticket $ticket)
    {
        $request->validate([
            'comment' => 'required|string|max:255',
        ]);

        Comment::create([
            'ticket_id' => $ticket->id,
            'user_id' => $request->user()->id,
            'comment' => $request->comment,
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Comment added.')]);

        return back();
    }
}

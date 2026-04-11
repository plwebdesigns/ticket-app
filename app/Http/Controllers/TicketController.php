<?php

namespace App\Http\Controllers;

use App\Enums\Priority;
use App\Http\Requests\StoreTicketRequest;
use App\Http\Requests\UpdateTicketRequest;
use App\Models\CurrentState;
use App\Models\Ticket;
use App\Models\Type;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class TicketController extends Controller
{
    public function show(Ticket $ticket): Response
    {
        $ticket->load([
            'type',
            'currentState',
            'requestedBy',
            'assignedTo',
            'createdBy',
            'updatedBy',
        ]);

        return Inertia::render('issues/Show', [
            'ticket' => $ticket,
            ...$this->ticketFormLookups(),
        ]);
    }

    public function index(): Response
    {
        return Inertia::render('issues/Index', [
            'tickets' => Ticket::query()
                ->with(['type', 'currentState', 'requestedBy', 'assignedTo'])
                ->latest()
                ->paginate(15),
            ...$this->ticketFormLookups(),
        ]);
    }

    public function store(StoreTicketRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request): void {
            $ticket = Ticket::query()->create([
                ...$request->validated(),
                'ticket_number' => 'TKT-TEMP-'.Str::uuid()->toString(),
                'created_by_id' => $request->user()->id,
                'updated_by_id' => $request->user()->id,
            ]);
            $ticket->update([
                'ticket_number' => 'TKT-'.str_pad((string) $ticket->id, 8, '0', STR_PAD_LEFT),
            ]);
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Ticket created.')]);

        return to_route('issues.index');
    }

    public function update(UpdateTicketRequest $request, Ticket $ticket): RedirectResponse
    {
        $ticket->update([
            ...$request->validated(),
            'updated_by_id' => $request->user()->id,
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Ticket updated.')]);

        return back(fallback: to_route('issues.show', $ticket));
    }

    public function destroy(Ticket $ticket): RedirectResponse
    {
        $ticket->deleted_by_id = auth()->id();
        $ticket->save();
        $ticket->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Ticket deleted.')]);

        return to_route('issues.index');
    }

    /**
     * @return array<string, mixed>
     */
    private function ticketFormLookups(): array
    {
        return [
            'types' => Type::query()->orderBy('name')->get(),
            'currentStates' => CurrentState::query()->orderBy('name')->get(),
            'users' => User::query()->orderBy('name')->get(['id', 'name', 'email']),
            'priorities' => collect(Priority::cases())->map(fn (Priority $p) => $p->value)->values(),
        ];
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TicketController extends ApiController
{
    public function cart(Request $request): JsonResponse
    {
        $tickets = Ticket::with(['event.venue', 'event.category'])
            ->where('user_id', $request->user()->user_id)
            ->whereNull('paid_at')
            ->where('status', 'cart')
            ->get()
            ->map(fn($t) => $this->format($t));
        return $this->success($tickets);
    }

    public function add(Request $request): JsonResponse
    {
        $data = $request->validate([
            'event_id' => 'required|integer|exists:events,event_id',
            'quantity' => 'integer|min:1|max:10',
        ]);
        $event = \App\Models\Event::find($data['event_id']);
        $ticket = Ticket::create([
            'event_id' => $event->event_id,
            'price'    => $event->price,
            'user_id'  => $request->user()->user_id,
            'quantity' => $data['quantity'] ?? 1,
            'status'   => 'cart',
        ]);
        return $this->success($this->format($ticket->load(['event.venue', 'event.category'])), 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $ticket = Ticket::where('ticket_id', $id)->where('user_id', $request->user()->user_id)->first();
        if (!$ticket) return $this->error('Билет не найден', 404);
        $ticket->fill($request->only('quantity'))->save();
        return $this->success($this->format($ticket->load(['event.venue', 'event.category'])));
    }

    public function remove(Request $request, int $id): JsonResponse
    {
        $deleted = Ticket::where('ticket_id', $id)
            ->where('user_id', $request->user()->user_id)
            ->whereNull('paid_at')
            ->delete();
        if (!$deleted) return $this->error('Билет не найден', 404);
        return $this->success(null, 200, 'Удалено');
    }

    public function checkout(Request $request): JsonResponse
    {
        $updated = Ticket::where('user_id', $request->user()->user_id)
            ->whereNull('paid_at')->where('status', 'cart')
            ->update(['paid_at' => now(), 'status' => 'paid']);
        if (!$updated) return $this->error('Корзина пуста', 422);
        return $this->success(['paid' => $updated], 200, 'Оплачено');
    }

    public function count(Request $request): JsonResponse
    {
        $count = Ticket::where('user_id', $request->user()->user_id)
            ->whereNull('paid_at')->where('status', 'cart')->count();
        return $this->success(['count' => $count]);
    }

    public function paid(Request $request): JsonResponse
    {
        $tickets = Ticket::with(['event.venue', 'event.category'])
            ->where('user_id', $request->user()->user_id)
            ->where('status', 'paid')->whereNotNull('paid_at')
            ->orderBy('paid_at', 'desc')->get()
            ->map(fn($t) => $this->format($t));
        return $this->success($tickets);
    }

    private function format(Ticket $t): array
    {
        return [
            'ticket_id'      => $t->ticket_id,
            'event_id'       => $t->event_id,
            'price'          => $t->price,
            'quantity'       => $t->quantity,
            'status'         => $t->status,
            'paid_at'        => $t->paid_at,
            'title'          => $t->event?->title,
            'start_datetime' => $t->event?->start_datetime,
            'image'          => $t->event?->image,
            'venue_name'     => $t->event?->venue?->name,
            'category_name'  => $t->event?->category?->name,
        ];
    }
}

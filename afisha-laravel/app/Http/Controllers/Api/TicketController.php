<?php

namespace App\Http\Controllers\Api;

use App\Mail\TicketConfirmationMail;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class TicketController extends ApiController
{
    private function requireUser(Request $request): ?\Illuminate\Http\JsonResponse
    {
        if ($request->user() instanceof \App\Models\Organization) {
            return $this->error('Организации не могут использовать корзину', 403);
        }
        return null;
    }

    public function cart(Request $request): JsonResponse
    {
        if ($err = $this->requireUser($request)) return $err;
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
        if ($err = $this->requireUser($request)) return $err;
        $data = $request->validate([
            'event_id' => 'required|integer|exists:events,event_id',
            'quantity' => 'integer|min:1|max:10',
        ]);
        $event    = \App\Models\Event::find($data['event_id']);
        $quantity = $data['quantity'] ?? 1;

        if ($event->capacity !== null) {
            $sold = Ticket::where('event_id', $event->event_id)
                ->whereIn('status', ['paid', 'return_pending', 'cart'])
                ->sum('quantity');
            if ($sold + $quantity > $event->capacity) {
                $left = max(0, $event->capacity - $sold);
                return $this->error($left > 0 ? "Осталось только {$left} мест" : 'Билеты закончились', 422);
            }
        }

        $ticket = Ticket::create([
            'event_id' => $event->event_id,
            'price'    => $event->price,
            'user_id'  => $request->user()->user_id,
            'quantity' => $quantity,
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
        if ($err = $this->requireUser($request)) return $err;
        $method    = $request->input('payment_method', 'card');
        $promoCode = strtoupper(trim($request->input('promo_code', '')));
        $user      = $request->user();

        $cartTickets = Ticket::with(['event.venue'])
            ->where('user_id', $user->user_id)
            ->whereNull('paid_at')->where('status', 'cart')
            ->get();

        if ($cartTickets->isEmpty()) return $this->error('Корзина пуста', 422);

        $now = now();
        Ticket::where('user_id', $user->user_id)
            ->whereNull('paid_at')->where('status', 'cart')
            ->update(['paid_at' => $now, 'status' => 'paid', 'payment_method' => $method]);

        if ($promoCode) {
            $promo = \App\Models\PromoCode::where('code', $promoCode)->where('is_active', true)->first();
            if ($promo && (!$promo->expires_at || !$promo->expires_at->isPast()) &&
                ($promo->max_uses === null || $promo->uses_count < $promo->max_uses)) {
                $promo->increment('uses_count');
            }
        }

        if ($user->email) {
            $payLabels = ['sbp' => 'СБП', 'card' => 'Банковская карта', 'sber' => 'СберПей', 'ymoney' => 'ЮMoney', 'tpay' => 'T-Pay', 'free' => 'Бесплатно'];
            $payLabel  = $payLabels[$method] ?? $method;
            $ticketData = $cartTickets->map(fn($t) => $this->format($t))->toArray();
            $total      = array_sum(array_map(fn($t) => (float)$t['price'] * (int)$t['quantity'], $ticketData));

            try {
                Mail::to($user->email)->send(new TicketConfirmationMail(
                    recipientName:  $user->first_name ?? 'Пользователь',
                    tickets:        $ticketData,
                    totalFormatted: number_format($total, 0, '.', ' ') . ' ₽',
                    paymentMethod:  $payLabel,
                ));
            } catch (\Throwable) {}
        }

        return $this->success(['paid' => $cartTickets->count()], 200, 'Оплачено');
    }

    public function requestReturn(Request $request, int $id): JsonResponse
    {
        $ticket = Ticket::where('ticket_id', $id)
            ->where('user_id', $request->user()->user_id)
            ->where('status', 'paid')
            ->first();
        if (!$ticket) return $this->error('Билет не найден или уже возвращён', 404);
        $ticket->update(['status' => 'return_pending']);
        return $this->success(null, 200, 'Заявка на возврат принята');
    }

    public function count(Request $request): JsonResponse
    {
        if ($request->user() instanceof \App\Models\Organization) return $this->success(['count' => 0]);
        $count = Ticket::where('user_id', $request->user()->user_id)
            ->whereNull('paid_at')->where('status', 'cart')->count();
        return $this->success(['count' => $count]);
    }

    public function paid(Request $request): JsonResponse
    {
        if ($err = $this->requireUser($request)) return $err;
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
            'payment_method' => $t->payment_method,
            'title'          => $t->event?->title,
            'start_datetime' => $t->event?->start_datetime,
            'image'          => $t->event?->image,
            'venue_name'     => $t->event?->venue?->name,
            'category_name'  => $t->event?->category?->name,
        ];
    }
}

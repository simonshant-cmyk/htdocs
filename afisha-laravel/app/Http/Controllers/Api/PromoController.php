<?php

namespace App\Http\Controllers\Api;

use App\Models\PromoCode;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PromoController extends ApiController
{
    private function requireOrg(Request $request): ?\Illuminate\Http\JsonResponse
    {
        if (!($request->user() instanceof \App\Models\Organization)) {
            return $this->error('Только для организаций', 403);
        }
        return null;
    }

    public function index(Request $request): JsonResponse
    {
        if ($err = $this->requireOrg($request)) return $err;
        $promos = PromoCode::where('organization_id', $request->user()->organization_id)
            ->orderBy('created_at', 'desc')->get();
        return $this->success($promos);
    }

    public function store(Request $request): JsonResponse
    {
        if ($err = $this->requireOrg($request)) return $err;
        $data = $request->validate([
            'code'           => 'required|string|max:30|unique:promo_codes,code',
            'discount_type'  => 'required|in:percent,fixed',
            'discount_value' => 'required|numeric|min:1',
            'max_uses'       => 'nullable|integer|min:1',
            'expires_at'     => 'nullable|date|after:now',
        ]);
        $promo = PromoCode::create([
            ...$data,
            'code'            => strtoupper($data['code']),
            'organization_id' => $request->user()->organization_id,
            'is_active'       => true,
            'uses_count'      => 0,
        ]);
        return $this->success($promo, 201);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        if ($err = $this->requireOrg($request)) return $err;
        $promo = PromoCode::where('id', $id)
            ->where('organization_id', $request->user()->organization_id)
            ->firstOrFail();
        $promo->delete();
        return $this->success(null, 200, 'Промокод удалён');
    }

    public function toggle(Request $request, int $id): JsonResponse
    {
        if ($err = $this->requireOrg($request)) return $err;
        $promo = PromoCode::where('id', $id)
            ->where('organization_id', $request->user()->organization_id)
            ->firstOrFail();
        $promo->update(['is_active' => !$promo->is_active]);
        return $this->success(['is_active' => $promo->is_active]);
    }

    public function validate(Request $request): JsonResponse
    {
        $request->validate(['code' => 'required|string']);
        $code = strtoupper(trim($request->code));

        $promo = PromoCode::where('code', $code)->where('is_active', true)->first();

        if (!$promo) return $this->error('Промокод не найден', 404);
        if ($promo->expires_at && $promo->expires_at->isPast()) return $this->error('Промокод истёк', 422);
        if ($promo->max_uses !== null && $promo->uses_count >= $promo->max_uses) return $this->error('Промокод исчерпан', 422);

        $user = $request->user();
        if (!($user instanceof \App\Models\User)) return $this->error('Только для пользователей', 403);

        $cartTotal = Ticket::where('user_id', $user->user_id)
            ->whereNull('paid_at')->where('status', 'cart')
            ->selectRaw('SUM(price * quantity) as total')->value('total') ?? 0;

        $discount = $promo->discount_type === 'percent'
            ? round($cartTotal * $promo->discount_value / 100, 2)
            : min($promo->discount_value, $cartTotal);

        return $this->success([
            'code'           => $promo->code,
            'discount_type'  => $promo->discount_type,
            'discount_value' => $promo->discount_value,
            'discount_amount'=> $discount,
            'final_total'    => max(0, $cartTotal - $discount),
        ]);
    }

    public function applyAndCheckout(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!($user instanceof \App\Models\User)) return $this->error('Нет доступа', 403);

        $method    = $request->input('payment_method', 'card');
        $promoCode = strtoupper(trim($request->input('promo_code', '')));

        $cartTickets = Ticket::with('event')
            ->where('user_id', $user->user_id)
            ->whereNull('paid_at')->where('status', 'cart')
            ->get();

        if ($cartTickets->isEmpty()) return $this->error('Корзина пуста', 422);

        $promo = null;
        if ($promoCode) {
            $promo = PromoCode::where('code', $promoCode)->where('is_active', true)->first();
            if (!$promo || ($promo->expires_at && $promo->expires_at->isPast()) ||
                ($promo->max_uses !== null && $promo->uses_count >= $promo->max_uses)) {
                $promo = null;
            }
        }

        if ($promo) {
            $promo->increment('uses_count');
        }

        $now = now();
        Ticket::where('user_id', $user->user_id)
            ->whereNull('paid_at')->where('status', 'cart')
            ->update(['paid_at' => $now, 'status' => 'paid', 'payment_method' => $method]);

        return $this->success([
            'paid'          => $cartTickets->count(),
            'promo_applied' => $promo?->code,
        ], 200, 'Оплачено');
    }
}

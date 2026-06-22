<?php

namespace Tests\Feature;

use App\Models\Ticket;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Тест-кейсы модуля билетов (TC-10 … TC-17 пояснительной записки).
 */
class TicketTest extends TestCase
{
    /** TC-10 — добавление билета в корзину → 201 */
    public function test_add_ticket_to_cart(): void
    {
        $user  = $this->createUser();
        $event = $this->createEvent(['capacity' => 100]);
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/tickets', [
            'event_id' => $event->event_id,
            'quantity' => 1,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('tickets', [
            'event_id' => $event->event_id,
            'user_id'  => $user->user_id,
            'status'   => 'cart',
        ]);
    }

    /** TC-11 — добавление при нехватке мест → 422 */
    public function test_add_ticket_when_no_seats_left_fails(): void
    {
        $user  = $this->createUser();
        $event = $this->createEvent(['capacity' => 1]);
        // Занимаем единственное место
        Ticket::create([
            'user_id'  => $this->createUser()->user_id,
            'event_id' => $event->event_id,
            'price'    => $event->price,
            'quantity' => 1,
            'status'   => 'paid',
        ]);
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/tickets', [
            'event_id' => $event->event_id,
            'quantity' => 1,
        ]);

        $response->assertStatus(422);
    }

    /** TC-12 — оформление заказа (чекаут) → 200, статусы → paid */
    public function test_checkout_marks_tickets_paid(): void
    {
        Mail::fake();
        $user  = $this->createUser(['email' => 'buyer@example.com']);
        $event = $this->createEvent();
        Ticket::create([
            'user_id'  => $user->user_id,
            'event_id' => $event->event_id,
            'price'    => 500,
            'quantity' => 1,
            'status'   => 'cart',
        ]);
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/tickets/checkout', [
            'payment_method' => 'card',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('tickets', [
            'user_id' => $user->user_id,
            'status'  => 'paid',
        ]);
    }

    /** TC-13 — чекаут с действующим промокодом → счётчик использований +1 */
    public function test_checkout_with_promo_increments_uses(): void
    {
        Mail::fake();
        $user  = $this->createUser();
        $event = $this->createEvent();
        Ticket::create([
            'user_id'  => $user->user_id,
            'event_id' => $event->event_id,
            'price'    => 500,
            'quantity' => 1,
            'status'   => 'cart',
        ]);
        \App\Models\PromoCode::create([
            'code'           => 'SUMMER25',
            'discount_type'  => 'percent',
            'discount_value' => 25,
            'uses_count'     => 0,
            'is_active'      => true,
        ]);
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/tickets/checkout', [
            'payment_method' => 'card',
            'promo_code'     => 'SUMMER25',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('promo_codes', ['code' => 'SUMMER25', 'uses_count' => 1]);
    }

    /** TC-14 — чекаут с пустой корзиной → 422 */
    public function test_checkout_with_empty_cart_fails(): void
    {
        $user = $this->createUser();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/tickets/checkout', []);

        $response->assertStatus(422)
            ->assertJson(['error' => 'Корзина пуста']);
    }

    /** TC-15 — заявка на возврат оплаченного билета → 200, статус → return_pending */
    public function test_request_return_for_paid_ticket(): void
    {
        $user  = $this->createUser();
        $event = $this->createEvent();
        $ticket = Ticket::create([
            'user_id'  => $user->user_id,
            'event_id' => $event->event_id,
            'price'    => 500,
            'quantity' => 1,
            'status'   => 'paid',
            'paid_at'  => now(),
        ]);
        Sanctum::actingAs($user);

        $response = $this->postJson("/api/tickets/{$ticket->ticket_id}/return");

        $response->assertStatus(200);
        $this->assertDatabaseHas('tickets', [
            'ticket_id' => $ticket->ticket_id,
            'status'    => 'return_pending',
        ]);
    }

    /** TC-16 — заявка на возврат чужого билета → 404 */
    public function test_request_return_for_foreign_ticket_fails(): void
    {
        $owner = $this->createUser();
        $other = $this->createUser();
        $event = $this->createEvent();
        $ticket = Ticket::create([
            'user_id'  => $owner->user_id,
            'event_id' => $event->event_id,
            'price'    => 500,
            'quantity' => 1,
            'status'   => 'paid',
            'paid_at'  => now(),
        ]);
        Sanctum::actingAs($other);

        $response = $this->postJson("/api/tickets/{$ticket->ticket_id}/return");

        $response->assertStatus(404);
    }

    /** TC-17 — доступ организации к корзине запрещён → 403 */
    public function test_organization_cannot_access_cart(): void
    {
        $org = $this->createOrg();
        Sanctum::actingAs($org);

        $response = $this->getJson('/api/tickets');

        $response->assertStatus(403);
    }
}

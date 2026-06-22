<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\PromoCode;
use App\Models\Ticket;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Тестирование граничных значений (TC-26 … TC-33 пояснительной записки).
 */
class BoundaryTest extends TestCase
{
    /** TC-26 — количество билетов = 10 (максимум) → принято */
    public function test_quantity_max_accepted(): void
    {
        $user  = $this->createUser();
        $event = $this->createEvent(['capacity' => 100]);
        Sanctum::actingAs($user);

        $this->postJson('/api/tickets', ['event_id' => $event->event_id, 'quantity' => 10])
            ->assertStatus(201);
    }

    /** TC-27 — количество билетов = 11 (сверх максимума) → ошибка валидации */
    public function test_quantity_over_max_rejected(): void
    {
        $user  = $this->createUser();
        $event = $this->createEvent(['capacity' => 100]);
        Sanctum::actingAs($user);

        $this->postJson('/api/tickets', ['event_id' => $event->event_id, 'quantity' => 11])
            ->assertStatus(422);
    }

    /** TC-28 — длительность блокировки = 365 (максимум) → принято */
    public function test_block_duration_max_accepted(): void
    {
        $target = $this->createUser();
        Sanctum::actingAs($this->createUser(['role_id' => UserRole::MODERATOR]));

        $this->postJson("/api/moderation/users/{$target->user_id}/block", [
            'reason'   => 'Тест',
            'duration' => 365,
        ])->assertStatus(200);
    }

    /** TC-29 — длительность блокировки = 366 → ошибка валидации */
    public function test_block_duration_over_max_rejected(): void
    {
        $target = $this->createUser();
        Sanctum::actingAs($this->createUser(['role_id' => UserRole::MODERATOR]));

        $this->postJson("/api/moderation/users/{$target->user_id}/block", [
            'reason'   => 'Тест',
            'duration' => 366,
        ])->assertStatus(422);
    }

    /** TC-30 — токен сброса пароля ровно в пределах 60 минут → принят */
    public function test_reset_token_within_window_accepted(): void
    {
        $this->createUser(['email' => 'real@example.com']);
        $token = 'fresh-token';
        \DB::table('password_resets')->insert([
            'email'      => 'real@example.com',
            'token'      => hash('sha256', $token),
            'type'       => 'user',
            'created_at' => now()->subMinutes(59),
        ]);

        $this->postJson('/api/auth/reset-password', [
            'token'    => $token,
            'email'    => 'real@example.com',
            'password' => 'newpassword123',
        ])->assertStatus(200);
    }

    /** TC-32 — промокод с превышенным лимитом использований игнорируется */
    public function test_promo_over_max_uses_ignored(): void
    {
        Mail::fake();
        $user  = $this->createUser();
        $event = $this->createEvent();
        Ticket::create([
            'user_id' => $user->user_id, 'event_id' => $event->event_id,
            'price'   => 500, 'quantity' => 1, 'status' => 'cart',
        ]);
        PromoCode::create([
            'code' => 'OLD', 'discount_type' => 'percent', 'discount_value' => 10,
            'max_uses' => 5, 'uses_count' => 5, 'is_active' => true,
        ]);
        Sanctum::actingAs($user);

        $this->postJson('/api/tickets/checkout', ['promo_code' => 'OLD'])
            ->assertStatus(200);
        // Счётчик не должен вырасти сверх лимита
        $this->assertDatabaseHas('promo_codes', ['code' => 'OLD', 'uses_count' => 5]);
    }

    /** TC-33 — истёкший промокод игнорируется */
    public function test_expired_promo_ignored(): void
    {
        Mail::fake();
        $user  = $this->createUser();
        $event = $this->createEvent();
        Ticket::create([
            'user_id' => $user->user_id, 'event_id' => $event->event_id,
            'price'   => 500, 'quantity' => 1, 'status' => 'cart',
        ]);
        PromoCode::create([
            'code' => 'EXPIRED', 'discount_type' => 'percent', 'discount_value' => 10,
            'uses_count' => 0, 'is_active' => true, 'expires_at' => now()->subDay(),
        ]);
        Sanctum::actingAs($user);

        $this->postJson('/api/tickets/checkout', ['promo_code' => 'EXPIRED'])
            ->assertStatus(200);
        $this->assertDatabaseHas('promo_codes', ['code' => 'EXPIRED', 'uses_count' => 0]);
    }
}

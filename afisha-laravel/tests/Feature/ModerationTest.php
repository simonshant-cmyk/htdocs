<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Ticket;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Тест-кейсы модуля модерации (TC-18 … TC-25 пояснительной записки).
 */
class ModerationTest extends TestCase
{
    private function moderator()
    {
        return $this->createUser(['role_id' => UserRole::MODERATOR]);
    }

    private function admin()
    {
        return $this->createUser(['role_id' => UserRole::ADMIN]);
    }

    /** TC-18 — одобрение мероприятия модератором → 200, статус → active, письма подписчикам */
    public function test_moderator_approves_event_and_notifies_subscribers(): void
    {
        Mail::fake();
        $org   = $this->createOrg();
        $event = $this->createEvent(['organization_id' => $org->organization_id, 'status_id' => 4]);
        $subscriber = $this->createUser(['email' => 'sub@example.com']);
        \App\Models\OrgSubscription::create([
            'user_id'         => $subscriber->user_id,
            'organization_id' => $org->organization_id,
        ]);
        Sanctum::actingAs($this->moderator());

        $response = $this->putJson("/api/moderation/events/{$event->event_id}", [
            'status_id' => 1,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('events', ['event_id' => $event->event_id, 'status_id' => 1]);
        Mail::assertSent(\App\Mail\EventPublishedMail::class);
    }

    /** TC-19 — отклонение организации без указания причины → 422 */
    public function test_reject_organization_without_reason_fails(): void
    {
        $org = $this->createOrg(['status_id' => 4]);
        Sanctum::actingAs($this->moderator());

        $response = $this->putJson("/api/moderation/orgs/{$org->organization_id}", [
            'status_id' => 3,
        ]);

        $response->assertStatus(422)
            ->assertJson(['error' => 'Укажите причину отклонения']);
    }

    /** TC-20 — отклонение организации с причиной → 200, письмо организации */
    public function test_reject_organization_with_reason(): void
    {
        Mail::fake();
        $org = $this->createOrg(['status_id' => 4, 'email' => 'org@example.com']);
        Sanctum::actingAs($this->moderator());

        $response = $this->putJson("/api/moderation/orgs/{$org->organization_id}", [
            'status_id'        => 3,
            'rejection_reason' => 'Некорректные данные ИНН',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('organization', ['organization_id' => $org->organization_id, 'status_id' => 3]);
        Mail::assertSent(\App\Mail\OrgStatusMail::class);
    }

    /** TC-21 — блокировка пользователя → 200, статус → blocked, токены отозваны */
    public function test_block_user_revokes_tokens(): void
    {
        $target = $this->createUser();
        $target->createToken('api'); // выдаём токен, который должен быть отозван
        Sanctum::actingAs($this->moderator());

        $response = $this->postJson("/api/moderation/users/{$target->user_id}/block", [
            'reason'   => 'Спам',
            'duration' => 7,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('users', ['user_id' => $target->user_id, 'status' => 'blocked']);
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id'   => $target->user_id,
            'tokenable_type' => \App\Models\User::class,
        ]);
    }

    /** TC-22 — попытка заблокировать модератора → 403 */
    public function test_cannot_block_moderator(): void
    {
        $target = $this->createUser(['role_id' => UserRole::MODERATOR]);
        Sanctum::actingAs($this->admin());

        $response = $this->postJson("/api/moderation/users/{$target->user_id}/block", [
            'reason'   => 'Тест',
            'duration' => 7,
        ]);

        $response->assertStatus(403);
    }

    /** TC-23 — доступ к модерации без роли (обычный пользователь) → 403 */
    public function test_regular_user_cannot_access_moderation(): void
    {
        Sanctum::actingAs($this->createUser(['role_id' => UserRole::USER]));

        $response = $this->getJson('/api/moderation/stats');

        $response->assertStatus(403);
    }

    /** TC-24 — одобрение заявки на возврат → 200, статус → returned */
    public function test_approve_return_request(): void
    {
        $user  = $this->createUser();
        $event = $this->createEvent();
        $ticket = Ticket::create([
            'user_id'  => $user->user_id,
            'event_id' => $event->event_id,
            'price'    => 500,
            'quantity' => 1,
            'status'   => 'return_pending',
            'paid_at'  => now(),
        ]);
        Sanctum::actingAs($this->moderator());

        $response = $this->postJson("/api/moderation/returns/{$ticket->ticket_id}/approve");

        $response->assertStatus(200);
        $this->assertDatabaseHas('tickets', ['ticket_id' => $ticket->ticket_id, 'status' => 'returned']);
    }

    /** TC-25 — смена роли самому себе → 403 */
    public function test_admin_cannot_change_own_role(): void
    {
        $admin = $this->admin();
        Sanctum::actingAs($admin);

        $response = $this->putJson("/api/moderation/users/{$admin->user_id}", [
            'role_id' => UserRole::MODERATOR,
        ]);

        $response->assertStatus(403);
    }
}

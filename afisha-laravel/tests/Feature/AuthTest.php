<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Тест-кейсы модуля аутентификации (TC-01 … TC-09 пояснительной записки).
 */
class AuthTest extends TestCase
{
    /** TC-01 — регистрация с корректными данными → 201 + токен */
    public function test_register_with_valid_data_returns_token(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'first_name' => 'Иван',
            'phone'      => '+79001234567',
            'password'   => '123456',
            'pd_consent' => true,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['data' => ['token', 'user']]);
        $this->assertDatabaseHas('users', ['phone' => '+79001234567']);
    }

    /** TC-02 — регистрация с уже занятым номером → 422 */
    public function test_register_with_taken_phone_fails(): void
    {
        $this->createUser(['phone' => '+79001234567']);

        $response = $this->postJson('/api/auth/register', [
            'first_name' => 'Пётр',
            'phone'      => '+79001234567',
            'password'   => '123456',
            'pd_consent' => true,
        ]);

        $response->assertStatus(422)
            ->assertJson(['error' => 'Телефон уже зарегистрирован']);
    }

    /** TC-03 — регистрация без согласия на обработку ПД → 422 */
    public function test_register_without_pd_consent_fails(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'first_name' => 'Иван',
            'phone'      => '+79001234567',
            'password'   => '123456',
            'pd_consent' => false,
        ]);

        $response->assertStatus(422);
    }

    /** TC-04 — регистрация со слишком коротким паролем → 422 */
    public function test_register_with_short_password_fails(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'first_name' => 'Иван',
            'phone'      => '+79001234567',
            'password'   => '123',
            'pd_consent' => true,
        ]);

        $response->assertStatus(422);
    }

    /** TC-05 — вход с верным телефоном и паролем → 200 + токен */
    public function test_login_with_valid_credentials(): void
    {
        $this->createUser([
            'phone'         => '+79001234567',
            'password_hash' => Hash::make('secret123'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'phone'    => '+79001234567',
            'password' => 'secret123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['token', 'user']]);
    }

    /** TC-06 — вход с неверным паролем → 401 */
    public function test_login_with_wrong_password_fails(): void
    {
        $this->createUser([
            'phone'         => '+79001234567',
            'password_hash' => Hash::make('secret123'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'phone'    => '+79001234567',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401);
    }

    /** TC-07 — вход заблокированного пользователя → 403 */
    public function test_login_blocked_user_fails(): void
    {
        $this->createUser([
            'phone'         => '+79001234567',
            'password_hash' => Hash::make('secret123'),
            'status'        => 'blocked',
            'blocked_until' => now()->addDays(5),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'phone'    => '+79001234567',
            'password' => 'secret123',
        ]);

        $response->assertStatus(403);
    }

    /** TC-08 — восстановление пароля для существующего email → 200 + письмо */
    public function test_forgot_password_sends_email(): void
    {
        Mail::fake();
        $this->createUser(['email' => 'real@example.com']);

        $response = $this->postJson('/api/auth/forgot-password', [
            'email' => 'real@example.com',
        ]);

        $response->assertStatus(200);
        Mail::assertSent(\App\Mail\ResetPasswordMail::class);
        $this->assertDatabaseHas('password_resets', ['email' => 'real@example.com']);
    }

    /** TC-09 — сброс пароля по истёкшему токену (старше 60 минут) → 422 */
    public function test_reset_password_with_expired_token_fails(): void
    {
        $this->createUser(['email' => 'real@example.com']);

        $token = 'plain-token-value';
        DB::table('password_resets')->insert([
            'email'      => 'real@example.com',
            'token'      => hash('sha256', $token),
            'type'       => 'user',
            'created_at' => now()->subMinutes(61),
        ]);

        $response = $this->postJson('/api/auth/reset-password', [
            'token'    => $token,
            'email'    => 'real@example.com',
            'password' => 'newpassword123',
        ]);

        $response->assertStatus(422);
    }
}

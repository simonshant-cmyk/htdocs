<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Вход через VK ID (OAuth 2.1 + PKCE) — актуальный протокол VK.
 */
class VkController extends Controller
{
    private const AUTH_URL    = 'https://id.vk.ru/authorize';
    private const TOKEN_URL   = 'https://id.vk.ru/oauth2/auth';
    private const API_VERSION = '5.199';

    private function redirectUri(): string
    {
        $configured = config('services.vkontakte.redirect');
        return Str::startsWith($configured, 'http') ? $configured : url($configured);
    }

    // GET /auth/vk/redirect — отправляем пользователя на VK ID
    public function redirect(Request $request)
    {
        $clientId = config('services.vkontakte.client_id');
        if (!$clientId) {
            return redirect('/login?vk_error=' . urlencode('Вход через ВК не настроен'));
        }

        $state    = Str::random(40);
        $verifier = $this->generateCodeVerifier();

        $request->session()->put('vk_oauth_state', $state);
        $request->session()->put('vk_oauth_verifier', $verifier);

        $url = self::AUTH_URL . '?' . http_build_query([
            'response_type'         => 'code',
            'client_id'             => $clientId,
            'redirect_uri'          => $this->redirectUri(),
            'scope'                 => 'email',
            'state'                 => $state,
            'code_challenge'        => $this->codeChallengeS256($verifier),
            'code_challenge_method' => 'S256',
        ]);

        return redirect()->away($url);
    }

    // GET /auth/vk/callback — VK возвращает code + device_id, обмениваем на токен
    public function callback(Request $request)
    {
        if ($request->filled('error')) {
            return $this->fail('Авторизация через ВК отменена');
        }

        $expectedState = $request->session()->pull('vk_oauth_state');
        $verifier      = $request->session()->pull('vk_oauth_verifier');

        if (!$expectedState || $request->input('state') !== $expectedState || !$verifier) {
            return $this->fail('Сессия авторизации устарела, попробуйте ещё раз');
        }

        $code = $request->input('code');
        if (!$code) {
            return $this->fail('ВК не вернул код авторизации');
        }

        // Обмен code → access_token (+ id_token с email)
        $tokenResp = Http::asForm()->post(self::TOKEN_URL, array_filter([
            'grant_type'    => 'authorization_code',
            'client_id'     => config('services.vkontakte.client_id'),
            'client_secret' => config('services.vkontakte.client_secret'),
            'redirect_uri'  => $this->redirectUri(),
            'code'          => $code,
            'code_verifier' => $verifier,
            'device_id'     => $request->input('device_id'),
        ]));

        $accessToken = $tokenResp->json('access_token');
        if (!$tokenResp->successful() || !$accessToken) {
            return $this->fail('Не удалось получить токен ВК');
        }

        $vkId  = (int) ($tokenResp->json('user_id') ?? 0);
        $email = $this->emailFromIdToken($tokenResp->json('id_token'));

        // Имя/фамилия + (на всякий случай) id через VK API
        $firstName = 'Пользователь';
        $lastName  = null;
        $profile = Http::get('https://api.vk.com/method/users.get', [
            'access_token' => $accessToken,
            'fields'       => 'first_name,last_name',
            'v'            => self::API_VERSION,
        ])->json('response.0');

        if ($profile) {
            $vkId      = (int) ($profile['id'] ?? $vkId);
            $firstName = $profile['first_name'] ?? $firstName;
            $lastName  = $profile['last_name'] ?? null;
        }

        if (!$vkId) {
            return $this->fail('Не удалось определить пользователя ВК');
        }

        $user = $this->findOrCreateUser($vkId, $email, $firstName, $lastName);

        if ($user->status === 'blocked') {
            return $this->fail('Аккаунт заблокирован');
        }

        $user->tokens()->delete();
        $apiToken = $user->createToken('api')->plainTextToken;

        return view('auth.vk-callback', [
            'token' => $apiToken,
            'user'  => $user,
        ]);
    }

    private function findOrCreateUser(int $vkId, ?string $email, string $firstName, ?string $lastName): User
    {
        // 1. Уже привязан по vk_id
        $user = User::where('vk_id', $vkId)->first();
        if ($user) {
            return $user;
        }

        // 2. Совпадение по email — привязываем ВК к существующему аккаунту
        if ($email) {
            $user = User::where('email', $email)->first();
            if ($user) {
                $user->update(['vk_id' => $vkId]);
                return $user;
            }
        }

        // 3. Новый пользователь
        $user = User::create([
            'first_name'    => $firstName,
            'last_name'     => $lastName,
            'phone'         => '',
            'email'         => $email,
            'vk_id'         => $vkId,
            'password_hash' => Hash::make(Str::random(40)),
            'pd_consent'    => true,
            'role_id'       => 2,
        ]);

        AuditLog::write('user_registered', 'user', $user->user_id, null, null, [
            'name'   => $user->full_name,
            'source' => 'vk',
        ]);

        return $user;
    }

    // ── PKCE helpers ──
    private function generateCodeVerifier(int $length = 64): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-._~';
        $s = '';
        for ($i = 0; $i < $length; $i++) {
            $s .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        }
        return $s;
    }

    private function codeChallengeS256(string $verifier): string
    {
        return rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '=');
    }

    // Email лежит в JWT id_token (claim "email"), если выдан scope email
    private function emailFromIdToken(?string $jwt): ?string
    {
        if (!$jwt) {
            return null;
        }
        $parts = explode('.', $jwt);
        if (count($parts) !== 3) {
            return null;
        }
        $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);
        return is_array($payload) ? ($payload['email'] ?? null) : null;
    }

    private function fail(string $message)
    {
        return redirect('/login?vk_error=' . urlencode($message));
    }
}

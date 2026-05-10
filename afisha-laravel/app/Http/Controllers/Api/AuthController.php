<?php

namespace App\Http\Controllers\Api;

use App\Models\AuditLog;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends ApiController
{
    private function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone);
        if (strlen($digits) === 10) $digits = '7' . $digits;
        if (str_starts_with($digits, '8')) $digits = '7' . substr($digits, 1);
        return '+' . $digits;
    }

    // POST /api/auth/register
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'first_name'    => 'required|string|max:100',
            'last_name'     => 'nullable|string|max:100',
            'patronymic'    => 'nullable|string|max:100',
            'phone'         => 'required|string',
            'email'         => 'nullable|email|max:200',
            'date_of_birth' => 'nullable|date',
            'password'      => 'required|string|min:6',
            'pd_consent'    => 'required|accepted',
        ], [], [
            'first_name'    => 'имя',
            'last_name'     => 'фамилия',
            'phone'         => 'телефон',
            'email'         => 'email',
            'date_of_birth' => 'дата рождения',
            'password'      => 'пароль',
            'pd_consent'    => 'согласие на обработку данных',
        ]);

        $phone = $this->normalizePhone($request->input('phone'));

        if (User::where('phone', $phone)->exists()) {
            return $this->error('Телефон уже зарегистрирован');
        }

        $user = User::create([
            'first_name'    => $request->input('first_name'),
            'last_name'     => $request->input('last_name'),
            'patronymic'    => $request->input('patronymic'),
            'phone'         => $phone,
            'email'         => $request->input('email'),
            'date_of_birth' => $request->input('date_of_birth'),
            'password_hash' => Hash::make($request->input('password')),
            'pd_consent'    => true,
            'role_id'       => 2,
        ]);

        AuditLog::write('user_registered', 'user', $user->user_id, null, null, [
            'name'  => $user->full_name,
            'phone' => $phone,
        ]);

        $token = $user->createToken('api')->plainTextToken;
        return $this->success(['token' => $token, 'user' => $user], 201);
    }

    // POST /api/auth/login
    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'phone'    => 'required|string',
            'password' => 'required|string',
        ]);

        $data['phone'] = $this->normalizePhone($data['phone']);
        $user = User::where('phone', $data['phone'])->first();

        if (!$user || !Hash::check($data['password'], $user->password_hash)) {
            return $this->error('Неверный телефон или пароль', 401);
        }

        // Автоматическое снятие временной блокировки
        if ($user->status === 'blocked' && $user->blocked_until && $user->blocked_until->isPast()) {
            $user->update(['status' => 'active', 'blocked_until' => null]);
        } elseif ($user->status === 'blocked') {
            $msg = $user->blocked_until
                ? 'Аккаунт заблокирован до ' . $user->blocked_until->format('d.m.Y')
                : 'Аккаунт заблокирован';
            return $this->error($msg, 403);
        }

        // Автоматическое снятие ограничений
        if ($user->restriction_until && $user->restriction_until->isPast()) {
            $user->update(['restriction_until' => null, 'status' => $user->warning_count > 0 ? 'warned' : 'active']);
        }

        $token = $user->createToken('api')->plainTextToken;
        return $this->success(['token' => $token, 'user' => $user]);
    }

    // POST /api/auth/org/register
    public function orgRegister(Request $request): JsonResponse
    {
        $request->validate([
            'full_name'  => 'required|string|max:200',
            'email'      => 'required|email',
            'inn'        => 'nullable|string|max:20',
            'password'   => 'required|string|min:6',
            'pd_consent' => 'required|accepted',
        ], [], [
            'full_name'  => 'название организации',
            'email'      => 'email',
            'password'   => 'пароль',
            'pd_consent' => 'согласие на обработку данных',
        ]);

        if (Organization::where('email', $request->input('email'))->exists()) {
            return $this->error('Email уже зарегистрирован');
        }

        $org = Organization::create([
            'full_name'     => $request->input('full_name'),
            'email'         => $request->input('email'),
            'inn'           => $request->input('inn'),
            'password_hash' => Hash::make($request->input('password')),
            'pd_consent'    => true,
            'status_id'     => 4,
            'type_id'       => 1,
        ]);

        AuditLog::write('org_registered', 'organization', $org->organization_id, null, null, [
            'name'  => $org->full_name,
            'email' => $org->email,
        ]);

        $token = $org->createToken('api')->plainTextToken;
        return $this->success(['token' => $token, 'organization' => $org], 201);
    }

    // POST /api/auth/org/login
    public function orgLogin(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $org = Organization::where('email', $data['email'])->first();

        if (!$org || !Hash::check($data['password'], $org->password_hash)) {
            return $this->error('Неверный email или пароль', 401);
        }

        $token = $org->createToken('api')->plainTextToken;
        return $this->success(['token' => $token, 'organization' => $org]);
    }

    // GET /api/auth/me
    public function me(Request $request): JsonResponse
    {
        return $this->success($request->user());
    }

    // PUT /api/auth/me
    public function updateMe(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user instanceof Organization) {
            $allowed = $request->only(['full_name', 'address', 'phone', 'website', 'image', 'inn', 'ogrn', 'kpp']);
        } else {
            $allowed = $request->only(['first_name', 'last_name', 'patronymic', 'phone', 'email', 'date_of_birth', 'avatar']);
        }
        $user->fill($allowed)->save();
        return $this->success($user, 200, 'Профиль обновлён');
    }
}

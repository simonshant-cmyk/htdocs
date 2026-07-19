<?php

namespace App\Http\Controllers\Api;

use App\Mail\ResetPasswordMail;
use App\Models\AuditLog;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

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
        $request->validate([
            'phone'    => 'nullable|string',
            'email'    => 'nullable|email',
            'password' => 'required|string',
        ]);

        if (!$request->phone && !$request->email) {
            return $this->error('Укажите телефон или email', 422);
        }

        if ($request->phone) {
            $phone = $this->normalizePhone($request->input('phone'));
            $user  = User::where('phone', $phone)->first();
        } else {
            $user = User::where('email', $request->input('email'))->first();
        }

        if (!$user) {
            return $this->error('Неверные данные для входа', 401);
        }

        if (!Hash::check($request->input('password'), $user->password_hash)) {
            return $this->error('Неправильно введён пароль', 401);
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

        $user->tokens()->delete();
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

        $org->tokens()->delete();
        $token = $org->createToken('api')->plainTextToken;
        return $this->success(['token' => $token, 'organization' => $org]);
    }

    // GET /api/orgs/{id}  (public)
    public function showOrg(int $id): JsonResponse
    {
        $org = Organization::find($id);
        if (!$org) return $this->error('Организация не найдена', 404);
        $ratingRow = \App\Models\Review::whereIn('event_id',
                \App\Models\Event::where('organization_id', $org->organization_id)->pluck('event_id')
            )
            ->whereNotNull('rating')
            ->selectRaw('AVG(rating) as avg_rating, COUNT(*) as review_count')
            ->first();

        return $this->success([
            'organization_id' => $org->organization_id,
            'full_name'       => $org->full_name,
            'image'           => $org->image,
            'address'         => $org->address,
            'phone'           => $org->phone,
            'website'         => $org->website,
            'status_id'       => $org->status_id,
            'avg_rating'      => $ratingRow?->avg_rating ? round((float)$ratingRow->avg_rating, 1) : null,
            'review_count'    => (int)($ratingRow?->review_count ?? 0),
        ]);
    }

    // GET /api/orgs  (public — approved only)
    public function listOrgs(Request $request): JsonResponse
    {
        $q = Organization::where('status_id', 1);

        if ($request->search) {
            $q->where('full_name', 'like', '%' . $request->search . '%');
        }

        $orgIds = $q->pluck('organization_id');

        $eventIdsByOrg = \App\Models\Event::whereIn('organization_id', $orgIds)
            ->where('status_id', 1)
            ->select('organization_id', 'event_id')
            ->get()
            ->groupBy('organization_id');

        $ratingsByOrg = DB::table('reviews')
            ->join('events', 'reviews.event_id', '=', 'events.event_id')
            ->whereIn('events.organization_id', $orgIds)
            ->whereNotNull('reviews.rating')
            ->select('events.organization_id', DB::raw('AVG(reviews.rating) as avg_rating'), DB::raw('COUNT(*) as review_count'))
            ->groupBy('events.organization_id')
            ->get()
            ->keyBy('organization_id');

        $subsByOrg = \App\Models\OrgSubscription::whereIn('organization_id', $orgIds)
            ->selectRaw('organization_id, COUNT(*) as cnt')
            ->groupBy('organization_id')
            ->pluck('cnt', 'organization_id');

        $orgs = $q->orderBy('full_name')->get()->map(function ($org) use ($eventIdsByOrg, $ratingsByOrg, $subsByOrg) {
            $id = $org->organization_id;
            $r  = $ratingsByOrg->get($id);
            return [
                'organization_id' => $id,
                'full_name'       => $org->full_name,
                'image'           => $org->image,
                'address'         => $org->address,
                'phone'           => $org->phone,
                'website'         => $org->website,
                'events_count'    => $eventIdsByOrg->get($id)?->count() ?? 0,
                'subs_count'      => (int)($subsByOrg->get($id) ?? 0),
                'avg_rating'      => $r ? round((float)$r->avg_rating, 1) : null,
                'review_count'    => $r ? (int)$r->review_count : 0,
            ];
        });

        return $this->success($orgs);
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
            if ($request->filled('email')) {
                $request->validate([
                    'email' => 'email|unique:organization,email,' . $user->organization_id . ',organization_id',
                ]);
            }
            $allowed = $request->only(['full_name', 'email', 'address', 'phone', 'website', 'image', 'inn', 'ogrn', 'kpp', 'description', 'vk']);
        } else {
            $allowed = $request->only(['first_name', 'last_name', 'patronymic', 'phone', 'email', 'date_of_birth', 'avatar']);
        }
        $user->fill($allowed)->save();
        return $this->success($user, 200, 'Профиль обновлён');
    }

    // POST /api/auth/forgot-password  (public)
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email']);
        $email = strtolower(trim($request->input('email')));

        $user = User::whereRaw('LOWER(email) = ?', [$email])->first();
        $org  = Organization::whereRaw('LOWER(email) = ?', [$email])->first();

        if (!$user && !$org) {
            // Always respond with success to avoid email enumeration
            return $this->success(null, 200, 'Если аккаунт с таким email существует, инструкция отправлена');
        }

        $type = $org ? 'org' : 'user';
        $name = $org ? ($org->full_name ?? '') : (($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));

        $token   = Str::random(64);
        $hashed  = hash('sha256', $token);

        DB::table('password_resets')->where('email', $email)->delete();
        DB::table('password_resets')->insert(['email' => $email, 'token' => $hashed, 'type' => $type, 'created_at' => now()]);

        $url = url('/reset-password') . '?token=' . $token . '&email=' . urlencode($email) . '&type=' . $type;
        Mail::to($email)->send(new ResetPasswordMail($url, trim($name)));

        return $this->success(null, 200, 'Если аккаунт с таким email существует, инструкция отправлена');
    }

    // POST /api/auth/reset-password  (public)
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'token'    => 'required|string',
            'email'    => 'required|email',
            'password' => 'required|string|min:8',
            'type'     => 'nullable|in:user,org',
        ]);

        $email  = strtolower(trim($request->input('email')));
        $token  = $request->input('token');
        $hashed = hash('sha256', $token);

        $record = DB::table('password_resets')
            ->where('email', $email)
            ->where('token', $hashed)
            ->first();

        if (!$record) {
            return $this->error('Ссылка недействительна или устарела', 422);
        }

        if (now()->diffInMinutes($record->created_at, true) > 60) {
            DB::table('password_resets')->where('email', $email)->delete();
            return $this->error('Ссылка истекла. Запросите новую.', 422);
        }

        $newHash = Hash::make($request->input('password'));
        $type    = $record->type ?? $request->input('type', 'user');

        if ($type === 'org') {
            $updated = Organization::whereRaw('LOWER(email) = ?', [$email])
                ->update(['password_hash' => $newHash]);
        } else {
            $updated = User::whereRaw('LOWER(email) = ?', [$email])
                ->update(['password_hash' => $newHash]);
        }

        if (!$updated) {
            return $this->error('Аккаунт не найден', 404);
        }

        DB::table('password_resets')->where('email', $email)->delete();

        return $this->success(null, 200, 'Пароль успешно изменён');
    }

    // POST /api/auth/change-password
    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password'     => 'required|string|min:8',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password_hash)) {
            return $this->error('Текущий пароль введён неверно', 422);
        }

        $user->password_hash = Hash::make($request->new_password);
        $user->save();

        return $this->success(null, 200, 'Пароль успешно изменён');
    }
}

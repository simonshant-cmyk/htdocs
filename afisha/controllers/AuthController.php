<?php
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../models/OrganizationModel.php';
require_once __DIR__ . '/../helpers/Auth.php';
require_once __DIR__ . '/../helpers/Response.php';

class AuthController {

    // Нормализует телефон к формату +7XXXXXXXXXX
    private function normalizePhone(string $phone): string {
        $digits = preg_replace('/\D/', '', $phone);
        if (strlen($digits) === 10) $digits = '7' . $digits;
        if (str_starts_with($digits, '8')) $digits = '7' . substr($digits, 1);
        return '+' . $digits;
    }

    // POST /api/auth/register
    public function register(): void {
        $data = json_decode(file_get_contents('php://input'), true);

        if (empty($data['phone']) || empty($data['password']) || empty($data['full_name'])) {
            Response::error('Поля full_name, phone, password обязательны');
        }

        $data['phone'] = $this->normalizePhone($data['phone']);

        $model = new UserModel();
        if ($model->findByPhone($data['phone'])) {
            Response::error('Телефон уже зарегистрирован');
        }

        $id = $model->create($data);
        $user = $model->findById($id);

        $token = Auth::generateToken([
            'user_id' => $user['user_id'],
            'role_id' => $user['role_id'],
            'type'    => 'user',
        ]);

        Response::success(['token' => $token, 'user' => $user], 'Регистрация успешна', 201);
    }

    // POST /api/auth/login
    public function login(): void {
        $data = json_decode(file_get_contents('php://input'), true);

        if (empty($data['phone']) || empty($data['password'])) {
            Response::error('Укажите phone и password');
        }

        $data['phone'] = $this->normalizePhone($data['phone']);

        $model = new UserModel();
        $user = $model->findByPhone($data['phone']);

        if (!$user || !password_verify($data['password'], $user['password_hash'])) {
            Response::error('Неверный телефон или пароль', 401);
        }

        $token = Auth::generateToken([
            'user_id' => $user['user_id'],
            'role_id' => $user['role_id'],
            'type'    => 'user',
        ]);

        unset($user['password_hash']);
        Response::success(['token' => $token, 'user' => $user]);
    }

    // POST /api/auth/org/register
    public function orgRegister(): void {
        $data = json_decode(file_get_contents('php://input'), true);

        if (empty($data['full_name']) || empty($data['email']) || empty($data['password'])) {
            Response::error('Поля full_name, email, password обязательны');
        }

        $model = new OrganizationModel();
        if ($model->findByEmail($data['email'])) {
            Response::error('Email уже зарегистрирован');
        }

        $id = $model->create($data);
        $org = $model->findById($id);

        $token = Auth::generateToken([
            'org_id' => $org['organization_id'],
            'type'   => 'organization',
        ]);

        Response::success(['token' => $token, 'organization' => $org], 'Регистрация организации успешна', 201);
    }

    // POST /api/auth/org/login
    public function orgLogin(): void {
        $data = json_decode(file_get_contents('php://input'), true);

        if (empty($data['email']) || empty($data['password'])) {
            Response::error('Укажите email и password');
        }

        $model = new OrganizationModel();
        $org = $model->findByEmail($data['email']);

        if (!$org || !password_verify($data['password'], $org['password_hash'])) {
            Response::error('Неверный email или пароль', 401);
        }

        $token = Auth::generateToken([
            'org_id' => $org['organization_id'],
            'type'   => 'organization',
        ]);

        unset($org['password_hash']);
        Response::success(['token' => $token, 'organization' => $org]);
    }

    // PUT /api/auth/me
    public function updateMe(): void {
        $payload = Auth::require();
        $data = json_decode(file_get_contents('php://input'), true);

        if ($payload['type'] === 'user') {
            (new UserModel())->update($payload['user_id'], $data);
            $user = (new UserModel())->findById($payload['user_id']);
            unset($user['password_hash']);
            Response::success($user, 'Профиль обновлён');
        } else {
            (new OrganizationModel())->update($payload['org_id'], $data);
            $org = (new OrganizationModel())->findById($payload['org_id']);
            unset($org['password_hash']);
            Response::success($org, 'Профиль обновлён');
        }
    }

    // GET /api/auth/me
    public function me(): void {
        $payload = Auth::require();
        if ($payload['type'] === 'user') {
            $user = (new UserModel())->findById($payload['user_id']);
            if (!$user) Response::error('Пользователь не найден', 404);
            unset($user['password_hash']);
            Response::success($user);
        } else {
            $org = (new OrganizationModel())->findById($payload['org_id']);
            if (!$org) Response::error('Организация не найдена', 404);
            unset($org['password_hash']);
            Response::success($org);
        }
    }
}

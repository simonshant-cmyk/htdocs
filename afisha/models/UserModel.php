<?php
require_once __DIR__ . '/BaseModel.php';

class UserModel extends BaseModel {

    public function findByEmail(string $email): ?array {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        return $stmt->fetch() ?: null;
    }

    public function findByPhone(string $phone): ?array {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE phone = ? LIMIT 1');
        $stmt->execute([$phone]);
        return $stmt->fetch() ?: null;
    }

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare(
            'SELECT u.*, r.name as role_name 
             FROM users u 
             LEFT JOIN roles r ON u.role_id = r.role_id 
             WHERE u.user_id = ?'
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data): int {
        $stmt = $this->db->prepare(
            'INSERT INTO users (full_name, email, phone, age, role_id, password_hash)
             VALUES (:full_name, :email, :phone, :age, :role_id, :password_hash)'
        );
        $stmt->execute([
            'full_name'     => $data['full_name'],
            'email'         => $data['email'] ?? null,
            'phone'         => $data['phone'],
            'age'           => $data['age'] ?? null,
            'role_id'       => $data['role_id'] ?? 2, // 2 = обычный пользователь
            'password_hash' => password_hash($data['password'], PASSWORD_BCRYPT),
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool {
        $fields = [];
        $params = [];
        foreach (['full_name','email','phone','age','avatar'] as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = :$field";
                $params[$field] = $data[$field];
            }
        }
        if (empty($fields)) return false;
        $params['id'] = $id;
        $stmt = $this->db->prepare('UPDATE users SET ' . implode(', ', $fields) . ' WHERE user_id = :id');
        return $stmt->execute($params);
    }
}

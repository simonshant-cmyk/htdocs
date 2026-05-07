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
        $row = $stmt->fetch();
        if (!$row) return null;
        // Compute full_name for frontend compatibility
        $row['full_name'] = trim(implode(' ', array_filter([
            $row['last_name'] ?? '',
            $row['first_name'] ?? '',
            $row['patronymic'] ?? '',
        ])));
        return $row;
    }

    public function create(array $data): int {
        $stmt = $this->db->prepare(
            'INSERT INTO users (last_name, first_name, patronymic, date_of_birth, email, phone, role_id, password_hash, pd_consent)
             VALUES (:last_name, :first_name, :patronymic, :date_of_birth, :email, :phone, :role_id, :password_hash, :pd_consent)'
        );
        $stmt->execute([
            'last_name'     => $data['last_name']     ?? null,
            'first_name'    => $data['first_name']    ?? null,
            'patronymic'    => $data['patronymic']    ?? null,
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'email'         => $data['email']         ?? null,
            'phone'         => $data['phone'],
            'role_id'       => $data['role_id']       ?? 2,
            'password_hash' => password_hash($data['password'], PASSWORD_BCRYPT),
            'pd_consent'    => !empty($data['pd_consent']) ? 1 : 0,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool {
        $allowed = ['last_name', 'first_name', 'patronymic', 'date_of_birth', 'email', 'phone', 'avatar'];
        $fields  = [];
        $params  = ['id' => $id];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $fields[]      = "$field = :$field";
                $params[$field] = $data[$field];
            }
        }
        if (empty($fields)) return false;
        $stmt = $this->db->prepare('UPDATE users SET ' . implode(', ', $fields) . ' WHERE user_id = :id');
        return $stmt->execute($params);
    }
}

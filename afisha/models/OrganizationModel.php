<?php
require_once __DIR__ . '/BaseModel.php';

class OrganizationModel extends BaseModel {

    public function findByEmail(string $email): ?array {
        $stmt = $this->db->prepare('SELECT * FROM organization WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        return $stmt->fetch() ?: null;
    }

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare(
            'SELECT o.*, ot.name as type_name, s.status_name
             FROM organization o
             LEFT JOIN organization_types ot ON o.type_id = ot.type_id
             LEFT JOIN statuses s ON o.status_id = s.status_id
             WHERE o.organization_id = ?'
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function update(int $id, array $data): bool {
        $allowed = ['full_name', 'email', 'address', 'inn', 'ogrn', 'kpp', 'phone', 'website', 'image'];
        $fields  = [];
        $params  = ['id' => $id];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $fields[]      = "$field = :$field";
                $params[$field] = $data[$field];
            }
        }
        if (empty($fields)) return false;
        $stmt = $this->db->prepare('UPDATE organization SET ' . implode(', ', $fields) . ' WHERE organization_id = :id');
        return $stmt->execute($params);
    }

    public function create(array $data): int {
        $stmt = $this->db->prepare(
            'INSERT INTO organization (full_name, address, inn, ogrn, kpp, phone, website, type_id, status_id, password_hash, email, pd_consent)
             VALUES (:full_name, :address, :inn, :ogrn, :kpp, :phone, :website, :type_id, :status_id, :password_hash, :email, :pd_consent)'
        );
        $stmt->execute([
            'full_name'     => $data['full_name'],
            'address'       => $data['address']  ?? null,
            'inn'           => $data['inn']       ?? null,
            'ogrn'          => $data['ogrn']      ?? null,
            'kpp'           => $data['kpp']       ?? null,
            'phone'         => $data['phone']     ?? null,
            'website'       => $data['website']   ?? null,
            'type_id'       => $data['type_id']   ?? null,
            'status_id'     => 4, // 4 = Ожидает подтверждения
            'password_hash' => password_hash($data['password'], PASSWORD_BCRYPT),
            'email'         => $data['email']     ?? null,
            'pd_consent'    => !empty($data['pd_consent']) ? 1 : 0,
        ]);
        return (int) $this->db->lastInsertId();
    }
}

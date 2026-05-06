<?php
require_once __DIR__ . '/BaseModel.php';

class VenueModel extends BaseModel {

    public function getAll(array $filters = []): array {
        $sql = 'SELECT v.*, c.name as category_name
                FROM venues v
                LEFT JOIN categories c ON v.category_id = c.id
                WHERE 1=1';
        $params = [];
        if (!empty($filters['category_id'])) {
            $sql .= ' AND v.category_id = :category_id';
            $params['category_id'] = $filters['category_id'];
        }
        if (!empty($filters['search'])) {
            $sql .= ' AND v.name LIKE :search';
            $params['search'] = '%' . $filters['search'] . '%';
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare(
            'SELECT v.*, c.name as category_name
             FROM venues v
             LEFT JOIN categories c ON v.category_id = c.id
             WHERE v.venue_id = ?'
        );
        $stmt->execute([$id]);
        $venue = $stmt->fetch();
        if (!$venue) return null;

        // Добавляем контакты
        $venue['contacts'] = $this->getContacts($id);
        // Добавляем расписание
        $venue['schedule'] = $this->getSchedule($id);
        return $venue;
    }

    public function getContacts(int $venueId): array {
        $stmt = $this->db->prepare('SELECT * FROM contacts WHERE venue_id = ?');
        $stmt->execute([$venueId]);
        return $stmt->fetchAll();
    }

    public function getSchedule(int $venueId): array {
        $stmt = $this->db->prepare('SELECT * FROM schedule WHERE venue_id = ?');
        $stmt->execute([$venueId]);
        return $stmt->fetchAll();
    }

    public function create(array $data): int {
        $stmt = $this->db->prepare(
            'INSERT INTO venues (name, address, category_id, age, description, image)
             VALUES (:name, :address, :category_id, :age, :description, :image)'
        );
        $stmt->execute([
            'name'        => $data['name'],
            'address'     => $data['address'] ?? null,
            'category_id' => $data['category_id'] ?? null,
            'age'         => $data['age'] ?? null,
            'description' => $data['description'] ?? null,
            'image'       => $data['image'] ?? null,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool {
        $fields = [];
        $params = ['id' => $id];
        $allowed = ['name', 'address', 'category_id', 'age', 'description', 'image'];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "$field = :$field";
                $params[$field] = $data[$field];
            }
        }
        if (empty($fields)) return false;
        $stmt = $this->db->prepare('UPDATE venues SET ' . implode(', ', $fields) . ' WHERE venue_id = :id');
        return $stmt->execute($params);
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare('DELETE FROM venues WHERE venue_id = ?');
        return $stmt->execute([$id]);
    }
}

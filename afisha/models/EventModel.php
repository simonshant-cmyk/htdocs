<?php
require_once __DIR__ . '/BaseModel.php';

class EventModel extends BaseModel {

    public function getAll(array $filters = []): array {
        $where = '1=1';
        $params = [];

        if (!empty($filters['category_id'])) {
            $where .= ' AND e.category_id = :category_id';
            $params['category_id'] = $filters['category_id'];
        }
        if (!empty($filters['organization_id'])) {
            $where .= ' AND e.organization_id = :organization_id';
            $params['organization_id'] = $filters['organization_id'];
        }
        if (!empty($filters['status_id'])) {
            $where .= ' AND e.status_id = :status_id';
            $params['status_id'] = $filters['status_id'];
        }
        if (!empty($filters['date_from'])) {
            $where .= ' AND e.start_datetime >= :date_from';
            $params['date_from'] = $filters['date_from'] . ' 00:00:00';
        }
        if (!empty($filters['date_to'])) {
            $where .= ' AND e.start_datetime <= :date_to';
            $params['date_to'] = $filters['date_to'] . ' 23:59:59';
        }
        if (!empty($filters['free'])) {
            $where .= ' AND (e.price = 0 OR e.price IS NULL)';
        }
        if (!empty($filters['search'])) {
            $where .= ' AND (e.title LIKE :search_t OR e.description LIKE :search_d)';
            $params['search_t'] = '%' . $filters['search'] . '%';
            $params['search_d'] = '%' . $filters['search'] . '%';
        }

        $orderMap = [
            'date_asc'   => 'e.start_datetime ASC',
            'date_desc'  => 'e.start_datetime DESC',
            'price_asc'  => 'e.price ASC',
            'price_desc' => 'e.price DESC',
        ];
        $order = $orderMap[$filters['sort'] ?? ''] ?? 'e.start_datetime ASC';

        $joins = 'FROM events e
                  LEFT JOIN categories c ON e.category_id = c.id
                  LEFT JOIN statuses s ON e.status_id = s.status_id
                  LEFT JOIN organization o ON e.organization_id = o.organization_id
                  LEFT JOIN venues v ON e.venue_id = v.venue_id
                  WHERE ' . $where;

        $countStmt = $this->db->prepare('SELECT COUNT(*) ' . $joins);
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $sql = 'SELECT e.*, c.name as category_name, s.status_name,
                       o.full_name as organization_name, o.status_id as org_status_id, v.name as venue_name '
             . $joins . ' ORDER BY ' . $order;

        $limit  = isset($filters['limit'])  ? (int) $filters['limit']  : null;
        $offset = isset($filters['offset']) ? (int) $filters['offset'] : 0;

        if ($limit !== null) {
            $sql .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $items = $stmt->fetchAll();

        if ($limit !== null) {
            return ['items' => $items, 'total' => $total, 'pages' => (int) ceil($total / $limit)];
        }
        return $items;
    }

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare(
            'SELECT e.*, 
                    c.name as category_name,
                    s.status_name,
                    o.full_name as organization_name, o.status_id as org_status_id,
                    v.name as venue_name, v.address as venue_address
             FROM events e
             LEFT JOIN categories c ON e.category_id = c.id
             LEFT JOIN statuses s ON e.status_id = s.status_id
             LEFT JOIN organization o ON e.organization_id = o.organization_id
             LEFT JOIN venues v ON e.venue_id = v.venue_id
             WHERE e.event_id = ?'
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data): int {
        $stmt = $this->db->prepare(
            'INSERT INTO events 
             (title, description, age_restriction, start_datetime, end_datetime, price, image, organization_id, venue_id, category_id, status_id)
             VALUES
             (:title, :description, :age_restriction, :start_datetime, :end_datetime, :price, :image, :organization_id, :venue_id, :category_id, :status_id)'
        );
        $stmt->execute([
            'title'           => $data['title'],
            'description'     => $data['description'] ?? null,
            'age_restriction' => $data['age_restriction'] ?? null,
            'start_datetime'  => $data['start_datetime'],
            'end_datetime'    => $data['end_datetime'],
            'price'           => $data['price'] ?? 0,
            'image'           => $data['image'] ?? null,
            'organization_id' => $data['organization_id'],
            'venue_id'        => $data['venue_id'] ?? null,
            'category_id'     => $data['category_id'] ?? null,
            'status_id'       => $data['status_id'] ?? 1,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool {
        $fields = [];
        $params = ['id' => $id];
        $allowed = ['title','description','age_restriction','start_datetime','end_datetime','price','image','venue_id','category_id','status_id'];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "$field = :$field";
                $params[$field] = $data[$field];
            }
        }
        if (empty($fields)) return false;
        $stmt = $this->db->prepare('UPDATE events SET ' . implode(', ', $fields) . ' WHERE event_id = :id');
        return $stmt->execute($params);
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare('DELETE FROM events WHERE event_id = ?');
        return $stmt->execute([$id]);
    }
}

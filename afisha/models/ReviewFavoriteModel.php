<?php
require_once __DIR__ . '/BaseModel.php';

class ReviewModel extends BaseModel {

    public function getByEvent(int $eventId): array {
        $stmt = $this->db->prepare(
            'SELECT r.*, u.full_name as user_name
             FROM reviews r
             LEFT JOIN users u ON r.user_id = u.user_id
             WHERE r.event_id = ?
             ORDER BY r.created_at DESC'
        );
        $stmt->execute([$eventId]);
        return $stmt->fetchAll();
    }

    public function getByVenue(int $venueId): array {
        $stmt = $this->db->prepare(
            'SELECT r.*, u.full_name as user_name
             FROM reviews r
             LEFT JOIN users u ON r.user_id = u.user_id
             WHERE r.venue_id = ?
             ORDER BY r.created_at DESC'
        );
        $stmt->execute([$venueId]);
        return $stmt->fetchAll();
    }

    public function create(array $data): int {
        $stmt = $this->db->prepare(
            'INSERT INTO reviews (user_id, text, rating, created_at, event_id, venue_id)
             VALUES (:user_id, :text, :rating, NOW(), :event_id, :venue_id)'
        );
        $stmt->execute([
            'user_id'  => $data['user_id'],
            'text'     => $data['text'],
            'rating'   => $data['rating'],
            'event_id' => $data['event_id'] ?? null,
            'venue_id' => $data['venue_id'] ?? null,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare('SELECT * FROM reviews WHERE review_id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function delete(int $userId, int $reviewId): bool {
        $stmt = $this->db->prepare('DELETE FROM reviews WHERE review_id = ? AND user_id = ?');
        return $stmt->execute([$reviewId, $userId]);
    }
}

class FavoriteModel extends BaseModel {

    public function getByUser(int $userId): array {
        $stmt = $this->db->prepare(
            'SELECT f.*,
                    e.title as event_title, e.start_datetime,
                    v.name as venue_name
             FROM favorites f
             LEFT JOIN events e ON f.event_id = e.event_id
             LEFT JOIN venues v ON f.venue_id = v.venue_id
             WHERE f.user_id = ?
             ORDER BY f.created_at DESC'
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function add(int $userId, ?int $eventId, ?int $venueId): int {
        $stmt = $this->db->prepare(
            'INSERT INTO favorites (user_id, event_id, venue_id, created_at)
             VALUES (:user_id, :event_id, :venue_id, NOW())'
        );
        $stmt->execute([
            'user_id'  => $userId,
            'event_id' => $eventId,
            'venue_id' => $venueId,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function remove(int $userId, int $favoriteId): bool {
        $stmt = $this->db->prepare('DELETE FROM favorites WHERE favorite_id = ? AND user_id = ?');
        return $stmt->execute([$favoriteId, $userId]);
    }

    public function exists(int $userId, ?int $eventId, ?int $venueId): bool {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM favorites WHERE user_id = ? AND event_id <=> ? AND venue_id <=> ?'
        );
        $stmt->execute([$userId, $eventId, $venueId]);
        return (bool) $stmt->fetchColumn();
    }
}

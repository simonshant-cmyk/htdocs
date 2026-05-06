<?php
require_once __DIR__ . '/BaseModel.php';

class TicketModel extends BaseModel {

    public function getCart(int $userId): array {
        $stmt = $this->db->prepare(
            'SELECT t.*, e.title, e.start_datetime, e.image, e.age_restriction,
                    v.name as venue_name, c.name as category_name
             FROM tickets t
             JOIN events e ON t.event_id = e.event_id
             LEFT JOIN venues v ON e.venue_id = v.venue_id
             LEFT JOIN categories c ON e.category_id = c.id
             WHERE t.user_id = ? AND t.status = "cart"
             ORDER BY t.created_at DESC'
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function getPaid(int $userId): array {
        $stmt = $this->db->prepare(
            'SELECT t.*, e.title, e.start_datetime, e.image,
                    v.name as venue_name, c.name as category_name
             FROM tickets t
             JOIN events e ON t.event_id = e.event_id
             LEFT JOIN venues v ON e.venue_id = v.venue_id
             LEFT JOIN categories c ON e.category_id = c.id
             WHERE t.user_id = ? AND t.status = "paid"
             ORDER BY t.paid_at DESC'
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function cartCount(int $userId): int {
        $stmt = $this->db->prepare(
            'SELECT COALESCE(SUM(quantity), 0) FROM tickets WHERE user_id = ? AND status = "cart"'
        );
        $stmt->execute([$userId]);
        return (int) $stmt->fetchColumn();
    }

    public function findInCart(int $userId, int $eventId): ?array {
        $stmt = $this->db->prepare(
            'SELECT * FROM tickets WHERE user_id = ? AND event_id = ? AND status = "cart" LIMIT 1'
        );
        $stmt->execute([$userId, $eventId]);
        return $stmt->fetch() ?: null;
    }

    public function add(int $userId, int $eventId, float $price, int $qty = 1): int {
        $existing = $this->findInCart($userId, $eventId);
        if ($existing) {
            $stmt = $this->db->prepare(
                'UPDATE tickets SET quantity = quantity + ? WHERE ticket_id = ?'
            );
            $stmt->execute([$qty, $existing['ticket_id']]);
            return $existing['ticket_id'];
        }
        $stmt = $this->db->prepare(
            'INSERT INTO tickets (user_id, event_id, quantity, price, status, created_at)
             VALUES (?, ?, ?, ?, "cart", NOW())'
        );
        $stmt->execute([$userId, $eventId, $qty, $price]);
        return (int) $this->db->lastInsertId();
    }

    public function updateQty(int $ticketId, int $userId, int $qty): bool {
        if ($qty <= 0) return $this->remove($ticketId, $userId);
        $stmt = $this->db->prepare(
            'UPDATE tickets SET quantity = ? WHERE ticket_id = ? AND user_id = ? AND status = "cart"'
        );
        return $stmt->execute([$qty, $ticketId, $userId]);
    }

    public function remove(int $ticketId, int $userId): bool {
        $stmt = $this->db->prepare(
            'DELETE FROM tickets WHERE ticket_id = ? AND user_id = ? AND status = "cart"'
        );
        return $stmt->execute([$ticketId, $userId]);
    }

    public function checkout(int $userId, string $paymentMethod): int {
        $stmt = $this->db->prepare(
            'UPDATE tickets SET status = "paid", payment_method = ?, paid_at = NOW()
             WHERE user_id = ? AND status = "cart"'
        );
        $stmt->execute([$paymentMethod, $userId]);
        return $stmt->rowCount();
    }
}

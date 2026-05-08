<?php
require_once __DIR__ . '/BaseModel.php';

class ModerationModel extends BaseModel {

    public function getStats(): array {
        return [
            'pending_orgs'  => (int) $this->db->query('SELECT COUNT(*) FROM organization WHERE status_id = 4')->fetchColumn(),
            'total_orgs'    => (int) $this->db->query('SELECT COUNT(*) FROM organization')->fetchColumn(),
            'total_reviews' => (int) $this->db->query('SELECT COUNT(*) FROM reviews')->fetchColumn(),
            'total_events'  => (int) $this->db->query('SELECT COUNT(*) FROM events')->fetchColumn(),
            'total_users'   => (int) $this->db->query('SELECT COUNT(*) FROM users')->fetchColumn(),
        ];
    }

    public function getOrganizations(bool $pendingOnly = true): array {
        $where = $pendingOnly ? 'WHERE o.status_id = 4' : '';
        $stmt = $this->db->query(
            "SELECT o.organization_id, o.full_name, o.email, o.address, o.inn,
                    o.status_id, o.rejection_reason, s.status_name,
                    ot.name as type_name,
                    COUNT(e.event_id) as events_count
             FROM organization o
             LEFT JOIN organization_types ot ON o.type_id = ot.type_id
             LEFT JOIN statuses s ON o.status_id = s.status_id
             LEFT JOIN events e ON e.organization_id = o.organization_id
             $where
             GROUP BY o.organization_id
             ORDER BY o.organization_id DESC"
        );
        return $stmt->fetchAll();
    }

    public function setOrgStatus(int $id, int $statusId, ?string $reason = null): bool {
        $stmt = $this->db->prepare(
            'UPDATE organization SET status_id = ?, rejection_reason = ? WHERE organization_id = ?'
        );
        return $stmt->execute([$statusId, $statusId === 3 ? $reason : null, $id]);
    }

    public function getReviews(): array {
        $stmt = $this->db->query(
            'SELECT r.review_id, r.text, r.rating, r.created_at,
                    TRIM(CONCAT_WS(" ", u.last_name, u.first_name, u.patronymic)) as user_name,
                    u.avatar as user_avatar,
                    e.title as event_title, e.event_id,
                    v.name as venue_name, v.venue_id
             FROM reviews r
             LEFT JOIN users u ON r.user_id = u.user_id
             LEFT JOIN events e ON r.event_id = e.event_id
             LEFT JOIN venues v ON r.venue_id = v.venue_id
             ORDER BY r.created_at DESC'
        );
        return $stmt->fetchAll();
    }

    public function deleteReview(int $id): bool {
        $stmt = $this->db->prepare('DELETE FROM reviews WHERE review_id = ?');
        return $stmt->execute([$id]);
    }

    public function getEvents(): array {
        $stmt = $this->db->query(
            'SELECT e.event_id, e.title, e.start_datetime, e.price, e.status_id,
                    c.name as category_name,
                    s.status_name,
                    o.full_name as organization_name,
                    v.name as venue_name
             FROM events e
             LEFT JOIN categories c ON e.category_id = c.id
             LEFT JOIN statuses s ON e.status_id = s.status_id
             LEFT JOIN organization o ON e.organization_id = o.organization_id
             LEFT JOIN venues v ON e.venue_id = v.venue_id
             ORDER BY e.start_datetime DESC'
        );
        return $stmt->fetchAll();
    }

    public function setEventStatus(int $id, int $statusId): bool {
        $stmt = $this->db->prepare('UPDATE events SET status_id = ? WHERE event_id = ?');
        return $stmt->execute([$statusId, $id]);
    }

    public function getUsers(): array {
        $stmt = $this->db->query(
            'SELECT u.user_id,
                    TRIM(CONCAT_WS(" ", u.last_name, u.first_name, u.patronymic)) as full_name,
                    u.email, u.phone, u.role_id, r.name as role_name,
                    u.date_of_birth,
                    (SELECT COUNT(*) FROM tickets t WHERE t.user_id = u.user_id AND t.paid_at IS NOT NULL) as tickets_count
             FROM users u
             LEFT JOIN roles r ON u.role_id = r.role_id
             ORDER BY u.user_id DESC'
        );
        return $stmt->fetchAll();
    }

    public function setUserRole(int $id, int $roleId): bool {
        $stmt = $this->db->prepare('UPDATE users SET role_id = ? WHERE user_id = ?');
        return $stmt->execute([$roleId, $id]);
    }
}

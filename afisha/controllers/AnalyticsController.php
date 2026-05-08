<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../helpers/Auth.php';
require_once __DIR__ . '/../helpers/Response.php';

class AnalyticsController {

    // GET /api/analytics/org
    public function orgStats(): void {
        $payload = Auth::require();
        if (($payload['type'] ?? '') !== 'organization') {
            Response::error('Только для организаций', 403);
        }
        $orgId = $payload['org_id'];
        $db = Database::getInstance()->getConnection();

        // Общая статистика
        $stmt = $db->prepare('
            SELECT
                COUNT(DISTINCT e.event_id) as total_events,
                COALESCE(SUM(CASE WHEN t.status = "paid" THEN t.quantity ELSE 0 END), 0) as total_tickets,
                COALESCE(SUM(CASE WHEN t.status = "paid" THEN t.price * t.quantity ELSE 0 END), 0) as total_revenue,
                COUNT(DISTINCT CASE WHEN t.status = "paid" THEN t.ticket_id END) as paid_tickets,
                COUNT(DISTINCT CASE WHEN t.status = "pending" THEN t.ticket_id END) as pending_tickets
            FROM events e
            LEFT JOIN tickets t ON e.event_id = t.event_id
            WHERE e.organization_id = ?
        ');
        $stmt->execute([$orgId]);
        $totals = $stmt->fetch();

        // Продажи по событиям (топ 10)
        $stmt = $db->prepare('
            SELECT e.event_id, e.title, e.start_datetime,
                COALESCE(SUM(CASE WHEN t.status = "paid" THEN t.quantity ELSE 0 END), 0) as tickets_sold,
                COALESCE(SUM(CASE WHEN t.status = "paid" THEN t.price * t.quantity ELSE 0 END), 0) as revenue
            FROM events e
            LEFT JOIN tickets t ON e.event_id = t.event_id
            WHERE e.organization_id = ?
            GROUP BY e.event_id, e.title, e.start_datetime
            ORDER BY e.start_datetime DESC
            LIMIT 10
        ');
        $stmt->execute([$orgId]);
        $byEvent = $stmt->fetchAll();

        // Продажи по дням (последние 30 дней)
        $stmt = $db->prepare('
            SELECT DATE(t.paid_at) as day,
                COUNT(t.ticket_id) as tickets,
                COALESCE(SUM(t.price * t.quantity), 0) as revenue
            FROM tickets t
            JOIN events e ON t.event_id = e.event_id
            WHERE e.organization_id = ? AND t.status = "paid"
              AND t.paid_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY DATE(t.paid_at)
            ORDER BY day ASC
        ');
        $stmt->execute([$orgId]);
        $byDay = $stmt->fetchAll();

        Response::success([
            'totals'   => $totals,
            'by_event' => $byEvent,
            'by_day'   => $byDay,
        ]);
    }
}

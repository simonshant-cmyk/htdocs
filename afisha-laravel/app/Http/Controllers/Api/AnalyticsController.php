<?php

namespace App\Http\Controllers\Api;

use App\Models\{Event, Ticket};
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends ApiController
{
    public function orgStats(Request $request): JsonResponse
    {
        $org = $request->user();
        if (!($org instanceof \App\Models\Organization)) {
            return $this->error('Только для организаций', 403);
        }
        $orgId = $org->organization_id;

        $totals = DB::selectOne("
            SELECT
                COUNT(DISTINCT e.event_id)                                   AS total_events,
                COALESCE(SUM(CASE WHEN t.paid_at IS NOT NULL THEN t.quantity END), 0) AS total_tickets,
                COALESCE(SUM(CASE WHEN t.paid_at IS NOT NULL THEN t.price * t.quantity END), 0) AS total_revenue,
                COALESCE(SUM(CASE WHEN t.paid_at IS NULL    THEN t.quantity END), 0) AS pending_tickets
            FROM events e
            LEFT JOIN tickets t ON t.event_id = e.event_id
            WHERE e.organization_id = ?
        ", [$orgId]);

        $byDay = DB::select("
            SELECT DATE(t.paid_at) AS day,
                   SUM(t.price * t.quantity) AS revenue,
                   SUM(t.quantity) AS tickets
            FROM tickets t
            JOIN events e ON t.event_id = e.event_id
            WHERE e.organization_id = ? AND t.paid_at IS NOT NULL
              AND t.paid_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY DATE(t.paid_at)
            ORDER BY day ASC
        ", [$orgId]);

        $byEvent = DB::select("
            SELECT e.event_id, e.title, e.start_datetime,
                   COALESCE(SUM(CASE WHEN t.paid_at IS NOT NULL THEN t.quantity END), 0) AS tickets_sold,
                   COALESCE(SUM(CASE WHEN t.paid_at IS NOT NULL THEN t.price * t.quantity END), 0) AS revenue
            FROM events e
            LEFT JOIN tickets t ON t.event_id = e.event_id
            WHERE e.organization_id = ?
            GROUP BY e.event_id
            ORDER BY e.start_datetime DESC
        ", [$orgId]);

        return $this->success([
            'totals'   => $totals,
            'by_day'   => $byDay,
            'by_event' => $byEvent,
        ]);
    }

    public function orgBuyers(Request $request): JsonResponse
    {
        $org = $request->user();
        if (!($org instanceof \App\Models\Organization)) {
            return $this->error('Только для организаций', 403);
        }
        $orgId = $org->organization_id;
        $eventId = $request->event_id;

        $q = DB::table('tickets as t')
            ->join('events as e', 't.event_id', '=', 'e.event_id')
            ->join('users as u', 't.user_id', '=', 'u.user_id')
            ->where('e.organization_id', $orgId)
            ->whereNotNull('t.paid_at')
            ->select(
                'u.user_id',
                DB::raw("CONCAT(u.last_name, ' ', u.first_name) as full_name"),
                'u.email', 'u.phone',
                'e.event_id', 'e.title as event_title',
                't.ticket_id', 't.quantity', 't.price', 't.paid_at'
            )
            ->orderBy('t.paid_at', 'desc');

        if ($eventId) $q->where('t.event_id', $eventId);

        return $this->success($q->limit(200)->get());
    }

    public function orgReviews(Request $request): JsonResponse
    {
        $org = $request->user();
        if (!($org instanceof \App\Models\Organization)) {
            return $this->error('Только для организаций', 403);
        }
        $orgId = $org->organization_id;

        $reviews = DB::table('reviews as r')
            ->join('events as e', 'r.event_id', '=', 'e.event_id')
            ->join('users as u', 'r.user_id', '=', 'u.user_id')
            ->where('e.organization_id', $orgId)
            ->select(
                'r.review_id', 'r.text', 'r.rating', 'r.created_at',
                DB::raw("CONCAT(u.last_name, ' ', u.first_name) as user_name"),
                'u.avatar as user_avatar',
                'e.event_id', 'e.title as event_title'
            )
            ->orderBy('r.created_at', 'desc')
            ->limit(100)
            ->get();

        return $this->success($reviews);
    }
}

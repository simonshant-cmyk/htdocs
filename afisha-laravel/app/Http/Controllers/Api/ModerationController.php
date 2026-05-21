<?php

namespace App\Http\Controllers\Api;

use App\Enums\{OrgStatus, UserRole, EventStatus};
use App\Mail\OrgStatusMail;
use App\Models\{AuditLog, Event, Organization, OrgSubscription, Review, Ticket, User};
use App\Mail\EventPublishedMail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ModerationController extends ApiController
{
    // ── Guards ─────────────────────────────────────────────────────────────

    private function requireModerator(Request $request): void
    {
        $user = $request->user();
        if (!($user instanceof User) ||
            !in_array((int)$user->role_id, [UserRole::ADMIN, UserRole::MODERATOR])) {
            abort(403, 'Недостаточно прав');
        }
    }

    private function requireAdmin(Request $request): void
    {
        $user = $request->user();
        if (!($user instanceof User) || (int)$user->role_id !== UserRole::ADMIN) {
            abort(403, 'Только администратор');
        }
    }

    private function actor(Request $request): User { return $request->user(); }

    // ── Stats ───────────────────────────────────────────────────────────────

    public function stats(Request $request): JsonResponse
    {
        $this->requireModerator($request);
        return $this->success([
            'pending_orgs'    => Organization::where('status_id', OrgStatus::PENDING)->count(),
            'pending_returns' => Ticket::where('status', 'return_pending')->count(),
            'total_orgs'      => Organization::count(),
            'total_reviews'   => Review::count(),
            'total_events'    => Event::count(),
            'total_users'     => User::count(),
            'blocked_users'   => User::where('status', 'blocked')->count(),
        ]);
    }

    // ── Organizations ───────────────────────────────────────────────────────

    public function organizations(Request $request): JsonResponse
    {
        $this->requireModerator($request);

        $q = Organization::withCount('events')->with('status');

        // Filter
        $filter = $request->filter ?? 'pending';
        if ($filter === 'pending')  $q->where('status_id', OrgStatus::PENDING);
        if ($filter === 'approved') $q->where('status_id', OrgStatus::APPROVED);
        if ($filter === 'rejected') $q->where('status_id', OrgStatus::REJECTED);

        // Search
        if ($s = $request->search) {
            $q->where(function ($sub) use ($s) {
                $sub->where('full_name', 'like', '%'.$s.'%')
                    ->orWhere('email', 'like', '%'.$s.'%')
                    ->orWhere('inn', 'like', '%'.$s.'%');
            });
        }

        // Events count range
        if ($request->filled('events_min')) $q->having('events_count', '>=', (int)$request->events_min);
        if ($request->filled('events_max')) $q->having('events_count', '<=', (int)$request->events_max);

        $orgs = $q->orderBy('organization_id', 'desc')->get()->map(fn($o) => [
            'organization_id'  => $o->organization_id,
            'full_name'        => $o->full_name,
            'email'            => $o->email,
            'inn'              => $o->inn,
            'address'          => $o->address,
            'status_id'        => $o->status_id,
            'status_name'      => $o->status?->status_name,
            'rejection_reason' => $o->rejection_reason,
            'events_count'     => $o->events_count,
        ]);
        return $this->success($orgs);
    }

    public function updateOrg(Request $request, int $id): JsonResponse
    {
        $this->requireModerator($request);
        $data     = $request->validate(['status_id' => 'required|integer', 'rejection_reason' => 'nullable|string']);
        $statusId = (int)$data['status_id'];

        if (!in_array($statusId, [OrgStatus::APPROVED, OrgStatus::REJECTED])) {
            return $this->error('Недопустимый статус');
        }
        if ($statusId === OrgStatus::REJECTED && empty($data['rejection_reason'])) {
            return $this->error('Укажите причину отклонения');
        }

        $org = Organization::findOrFail($id);
        $org->status_id        = $statusId;
        $org->rejection_reason = $statusId === OrgStatus::REJECTED ? $data['rejection_reason'] : null;
        $org->save();

        $actor = $this->actor($request);
        AuditLog::write(
            $statusId === OrgStatus::APPROVED ? 'org_approved' : 'org_rejected',
            'organization', $id, $actor->user_id, $actor->role_id,
            ['name' => $org->full_name, 'reason' => $data['rejection_reason'] ?? null]
        );

        if ($org->email) {
            try {
                Mail::to($org->email)->send(new OrgStatusMail(
                    orgName:         $org->full_name,
                    approved:        $statusId === OrgStatus::APPROVED,
                    rejectionReason: $data['rejection_reason'] ?? null,
                ));
            } catch (\Throwable) {}
        }

        return $this->success(null, 200, $statusId === OrgStatus::APPROVED ? 'Организация одобрена' : 'Организация отклонена');
    }

    // ── Reviews ─────────────────────────────────────────────────────────────

    public function reviews(Request $request): JsonResponse
    {
        $this->requireModerator($request);
        $reviews = Review::with(['user', 'event', 'venue'])
            ->orderBy('created_at', 'desc')->get()
            ->map(fn($r) => [
                'review_id'   => $r->review_id,
                'text'        => $r->text,
                'rating'      => $r->rating,
                'created_at'  => $r->created_at,
                'user_id'     => $r->user_id,
                'user_name'   => $r->user?->full_name,
                'user_avatar' => $r->user?->avatar,
                'user_status' => $r->user?->status,
                'event_title' => $r->event?->title,
                'event_id'    => $r->event_id,
                'venue_name'  => $r->venue?->name,
                'venue_id'    => $r->venue_id,
            ]);
        return $this->success($reviews);
    }

    public function deleteReview(Request $request, int $id): JsonResponse
    {
        $this->requireModerator($request);
        $review = Review::findOrFail($id);
        $actor  = $this->actor($request);
        AuditLog::write('review_deleted', 'review', $id, $actor->user_id, $actor->role_id, [
            'text'       => mb_substr($review->text, 0, 100),
            'user_id'    => $review->user_id,
            'event_id'   => $review->event_id,
            'venue_id'   => $review->venue_id,
        ]);
        $review->delete();
        return $this->success(null, 200, 'Отзыв удалён');
    }

    // ── Events ──────────────────────────────────────────────────────────────

    public function events(Request $request): JsonResponse
    {
        $this->requireModerator($request);
        $events = Event::with(['category', 'status', 'organization', 'venue'])
            ->orderBy('start_datetime', 'desc')->get()
            ->map(fn($e) => [
                'event_id'          => $e->event_id,
                'title'             => $e->title,
                'start_datetime'    => $e->start_datetime,
                'price'             => $e->price,
                'status_id'         => $e->status_id,
                'category_name'     => $e->category?->name,
                'status_name'       => $e->status?->status_name,
                'organization_name' => $e->organization?->full_name,
                'venue_name'        => $e->venue?->name,
            ]);
        return $this->success($events);
    }

    public function updateEvent(Request $request, int $id): JsonResponse
    {
        $this->requireModerator($request);
        $statusId = (int)$request->validate(['status_id' => 'required|integer'])['status_id'];

        if (!in_array($statusId, [EventStatus::ACTIVE, EventStatus::COMPLETED, EventStatus::CANCELLED, EventStatus::PENDING])) {
            return $this->error('Недопустимый статус');
        }

        $event      = Event::findOrFail($id);
        $prevStatus = (int) $event->status_id;
        $event->update(['status_id' => $statusId]);

        $actor = $this->actor($request);
        AuditLog::write('event_status_changed', 'event', $id, $actor->user_id, $actor->role_id, [
            'title'     => $event->title,
            'status_id' => $statusId,
        ]);

        if ($statusId === EventStatus::ACTIVE && $prevStatus !== EventStatus::ACTIVE) {
            $event->load(['organization', 'venue']);
            $subscriberUserIds = OrgSubscription::where('organization_id', $event->organization_id)
                ->pluck('user_id');
            if ($subscriberUserIds->isNotEmpty()) {
                $subscribers = User::whereIn('user_id', $subscriberUserIds)
                    ->whereNotNull('email')->get();
                foreach ($subscribers as $subscriber) {
                    try {
                        Mail::to($subscriber->email)->send(new EventPublishedMail(
                            recipientName: $subscriber->first_name ?? 'Пользователь',
                            eventTitle:    $event->title,
                            eventDate:     $event->start_datetime,
                            venueName:     $event->venue?->name,
                            eventId:       $event->event_id,
                            orgName:       $event->organization?->full_name ?? '',
                        ));
                    } catch (\Throwable) {}
                }
            }
        }

        return $this->success(null, 200, 'Статус события обновлён');
    }

    // ── Users ────────────────────────────────────────────────────────────────

    public function users(Request $request): JsonResponse
    {
        $this->requireModerator($request);

        $q = User::withCount(['tickets as tickets_count' => fn($q) => $q->whereNotNull('paid_at')]);

        // Search
        if ($s = $request->search) {
            $q->where(function ($sub) use ($s) {
                $sub->where('first_name', 'like', '%'.$s.'%')
                    ->orWhere('last_name',  'like', '%'.$s.'%')
                    ->orWhere('phone',      'like', '%'.$s.'%')
                    ->orWhere('email',      'like', '%'.$s.'%');
            });
        }

        // Status filter
        if ($request->filled('status')) $q->where('status', $request->status);

        // Role filter
        if ($request->filled('role')) $q->where('role_id', (int)$request->role);

        // Age filter (derived from date_of_birth)
        if ($request->filled('age_from')) {
            $q->where('date_of_birth', '<=', now()->subYears((int)$request->age_from)->toDateString());
        }
        if ($request->filled('age_to')) {
            $q->where('date_of_birth', '>=', now()->subYears((int)$request->age_to + 1)->addDay()->toDateString());
        }

        $users = $q->orderBy('user_id', 'desc')->get()->map(fn($u) => [
            'user_id'          => $u->user_id,
            'full_name'        => $u->full_name,
            'email'            => $u->email,
            'phone'            => $u->phone,
            'date_of_birth'    => $u->date_of_birth,
            'role_id'          => $u->role_id,
            'status'           => $u->status,
            'warning_count'    => $u->warning_count,
            'blocked_until'    => $u->blocked_until,
            'restriction_until'=> $u->restriction_until,
            'tickets_count'    => $u->tickets_count,
        ]);
        return $this->success($users);
    }

    public function updateUser(Request $request, int $id): JsonResponse
    {
        // Only admin can change roles
        $this->requireAdmin($request);
        $roleId = (int)$request->validate(['role_id' => 'required|integer'])['role_id'];

        if (!in_array($roleId, [UserRole::USER, UserRole::MODERATOR])) {
            return $this->error('Недопустимая роль');
        }
        if ($id === $request->user()->user_id) {
            return $this->error('Нельзя изменить роль самому себе', 403);
        }

        $user = User::findOrFail($id);
        $oldRole = $user->role_id;
        $user->update(['role_id' => $roleId]);

        $actor = $this->actor($request);
        AuditLog::write('role_changed', 'user', $id, $actor->user_id, $actor->role_id, [
            'name'     => $user->full_name,
            'old_role' => $oldRole,
            'new_role' => $roleId,
        ]);

        return $this->success(null, 200, 'Роль обновлена');
    }

    // ── User moderation actions ──────────────────────────────────────────────

    public function warnUser(Request $request, int $id): JsonResponse
    {
        $this->requireModerator($request);
        $data   = $request->validate(['reason' => 'required|string|max:500']);
        $user   = User::findOrFail($id);
        $actor  = $this->actor($request);

        if (in_array($user->role_id, [UserRole::ADMIN, UserRole::MODERATOR])) {
            return $this->error('Нельзя применить меры к модератору или администратору', 403);
        }

        $user->increment('warning_count');
        $user->refresh();
        if ($user->status === 'active') {
            $user->update(['status' => 'warned']);
        }

        AuditLog::write('user_warned', 'user', $id, $actor->user_id, $actor->role_id, [
            'name'          => $user->full_name,
            'reason'        => $data['reason'],
            'warning_count' => $user->warning_count,
        ]);

        return $this->success(['warning_count' => $user->warning_count], 200, 'Предупреждение выдано');
    }

    public function blockUser(Request $request, int $id): JsonResponse
    {
        $this->requireModerator($request);
        $data  = $request->validate([
            'reason'   => 'required|string|max:500',
            'duration' => 'nullable|integer|min:1|max:365',
        ]);
        $user  = User::findOrFail($id);
        $actor = $this->actor($request);

        if (in_array($user->role_id, [UserRole::ADMIN, UserRole::MODERATOR])) {
            return $this->error('Нельзя заблокировать модератора или администратора', 403);
        }

        $blockedUntil = isset($data['duration']) ? now()->addDays($data['duration']) : null;
        $user->update(['status' => 'blocked', 'blocked_until' => $blockedUntil]);
        // Revoke all tokens
        $user->tokens()->delete();

        AuditLog::write('user_blocked', 'user', $id, $actor->user_id, $actor->role_id, [
            'name'          => $user->full_name,
            'reason'        => $data['reason'],
            'blocked_until' => $blockedUntil?->toIso8601String(),
        ]);

        return $this->success(null, 200, 'Пользователь заблокирован');
    }

    public function unblockUser(Request $request, int $id): JsonResponse
    {
        $this->requireModerator($request);
        $user  = User::findOrFail($id);
        $actor = $this->actor($request);

        $user->update([
            'status'       => $user->warning_count > 0 ? 'warned' : 'active',
            'blocked_until'=> null,
        ]);

        AuditLog::write('user_unblocked', 'user', $id, $actor->user_id, $actor->role_id, [
            'name' => $user->full_name,
        ]);

        return $this->success(null, 200, 'Блокировка снята');
    }

    public function restrictUser(Request $request, int $id): JsonResponse
    {
        $this->requireModerator($request);
        $data  = $request->validate([
            'reason'   => 'required|string|max:500',
            'duration' => 'nullable|integer|min:1|max:365',
        ]);
        $user  = User::findOrFail($id);
        $actor = $this->actor($request);

        if (in_array($user->role_id, [UserRole::ADMIN, UserRole::MODERATOR])) {
            return $this->error('Нельзя ограничить модератора или администратора', 403);
        }

        $restrictedUntil = isset($data['duration']) ? now()->addDays($data['duration']) : null;
        $user->update([
            'status'            => 'restricted',
            'restriction_until' => $restrictedUntil,
        ]);

        AuditLog::write('user_restricted', 'user', $id, $actor->user_id, $actor->role_id, [
            'name'              => $user->full_name,
            'reason'            => $data['reason'],
            'restriction_until' => $restrictedUntil?->toIso8601String(),
        ]);

        return $this->success(null, 200, 'Ограничение наложено');
    }

    // ── Return Requests ──────────────────────────────────────────────────────

    public function returns(Request $request): JsonResponse
    {
        $this->requireModerator($request);
        $tickets = Ticket::with(['user', 'event.venue'])
            ->where('status', 'return_pending')
            ->orderBy('ticket_id', 'desc')
            ->get()
            ->map(fn($t) => [
                'ticket_id'      => $t->ticket_id,
                'event_id'       => $t->event_id,
                'event_title'    => $t->event?->title,
                'event_date'     => $t->event?->start_datetime,
                'venue_name'     => $t->event?->venue?->name,
                'price'          => $t->price,
                'quantity'       => $t->quantity,
                'payment_method' => $t->payment_method,
                'paid_at'        => $t->paid_at,
                'user_id'        => $t->user_id,
                'user_name'      => $t->user?->full_name,
                'user_phone'     => $t->user?->phone,
                'user_email'     => $t->user?->email,
            ]);
        return $this->success($tickets);
    }

    public function approveReturn(Request $request, int $id): JsonResponse
    {
        $this->requireModerator($request);
        $ticket = Ticket::where('ticket_id', $id)->where('status', 'return_pending')->firstOrFail();
        $ticket->load('event');
        $ticket->update(['status' => 'returned']);
        $actor = $this->actor($request);
        AuditLog::write('return_approved', 'ticket', $id, $actor->user_id, $actor->role_id, [
            'event_title' => $ticket->event?->title,
            'user_id'     => $ticket->user_id,
            'amount'      => (float)$ticket->price * (int)$ticket->quantity,
        ]);
        return $this->success(null, 200, 'Возврат одобрен');
    }

    public function rejectReturn(Request $request, int $id): JsonResponse
    {
        $this->requireModerator($request);
        $ticket = Ticket::where('ticket_id', $id)->where('status', 'return_pending')->firstOrFail();
        $ticket->load('event');
        $ticket->update(['status' => 'paid']);
        $actor = $this->actor($request);
        AuditLog::write('return_rejected', 'ticket', $id, $actor->user_id, $actor->role_id, [
            'event_title' => $ticket->event?->title,
            'user_id'     => $ticket->user_id,
        ]);
        return $this->success(null, 200, 'Возврат отклонён');
    }

    // ── Audit Logs ───────────────────────────────────────────────────────────

    public function logs(Request $request): JsonResponse
    {
        $this->requireModerator($request);
        $actor    = $this->actor($request);
        $isAdmin  = (int)$actor->role_id === UserRole::ADMIN;

        $q = AuditLog::orderBy('created_at', 'desc');

        // Moderators only see registration events
        if (!$isAdmin) {
            $q->whereIn('action', ['user_registered', 'org_registered']);
        }

        // Admin can filter by action type
        if ($isAdmin && $request->filled('action')) {
            $q->where('action', $request->action);
        }

        $logs = $q->limit(200)->get()->map(fn($l) => [
            'log_id'      => $l->log_id,
            'action'      => $l->action,
            'actor_id'    => $l->actor_id,
            'actor_role'  => $l->actor_role,
            'target_type' => $l->target_type,
            'target_id'   => $l->target_id,
            'details'     => $l->details,
            'created_at'  => $l->created_at,
        ]);

        return $this->success($logs);
    }
}

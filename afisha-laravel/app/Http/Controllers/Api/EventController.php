<?php

namespace App\Http\Controllers\Api;

use App\Enums\OrgStatus;
use App\Models\Event;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $q = Event::query()
            ->with(['organization', 'category', 'status', 'venue'])
            ->select('events.*');

        if ($request->category_id) $q->where('category_id', $request->category_id);
        if ($request->organization_id) {
            $q->where('organization_id', $request->organization_id);
            // Hide drafts unless authenticated as the owner org
            $authUser = $request->user();
            if (!($authUser instanceof \App\Models\Organization) ||
                (int)$authUser->organization_id !== (int)$request->organization_id) {
                $q->where('events.status_id', '!=', 5);
            }
        } else {
            $q->where('events.status_id', '!=', 5); // hide drafts from public
        }
        if ($request->status_id) $q->where('events.status_id', $request->status_id);
        if ($request->date_from) $q->where('start_datetime', '>=', $request->date_from);
        if ($request->date_to)   $q->where('start_datetime', '<=', $request->date_to);
        if ($request->free)        $q->where('price', 0);
        if ($request->has_tickets) $q->whereNotNull('capacity')->whereRaw('capacity > (SELECT COALESCE(SUM(quantity),0) FROM tickets WHERE tickets.event_id = events.event_id AND tickets.status IN (\'paid\',\'return_pending\'))');
        if ($request->age_restriction !== null && $request->age_restriction !== '') $q->where('age_restriction', (int)$request->age_restriction);
        if ($request->search) {
            $term = $request->search;
            $q->where(function ($sub) use ($term) {
                $sub->whereRaw('MATCH(title, description) AGAINST(? IN BOOLEAN MODE)', [$term.'*'])
                    ->orWhere('title', 'like', '%'.$term.'%');
            });
        }

        $sort = $request->sort ?? 'date_asc';
        match ($sort) {
            'date_desc' => $q->orderBy('start_datetime', 'desc'),
            'price_asc' => $q->orderBy('price', 'asc'),
            'price_desc'=> $q->orderBy('price', 'desc'),
            default     => $q->orderBy('start_datetime', 'asc'),
        };

        if ($request->limit) {
            $total  = (clone $q)->count();
            $limit  = (int)$request->limit;
            $pages  = max(1, (int)ceil($total / $limit));
            $events = $q->limit($limit)->offset((int)($request->offset ?? 0))->get()->map(fn($e) => $this->formatEvent($e));
            return $this->success(['items' => $events, 'pages' => $pages, 'total' => $total]);
        }

        $events = $q->get()->map(fn($e) => $this->formatEvent($e));
        return $this->success($events);
    }

    public function show(int $id): JsonResponse
    {
        $event = Event::with(['organization', 'category', 'status', 'venue'])->find($id);
        if (!$event) return $this->error('Событие не найдено', 404);

        if ((int)$event->status_id === 5) {
            $authUser = request()->user();
            if (!($authUser instanceof \App\Models\Organization) ||
                (int)$authUser->organization_id !== (int)$event->organization_id) {
                return $this->error('Событие не найдено', 404);
            }
        }

        return $this->success($this->formatEvent($event));
    }

    public function store(Request $request): JsonResponse
    {
        $org = $request->user();
        if (!($org instanceof \App\Models\Organization)) {
            return $this->error('Только организации могут создавать события', 403);
        }
        if ((int)$org->status_id !== OrgStatus::APPROVED) {
            return $this->error('Аккаунт организации ещё не одобрен модератором', 403);
        }

        $asDraft = (bool)$request->input('as_draft', false);

        $data = $request->validate([
            'title'          => 'required|string|max:200',
            'start_datetime' => $asDraft ? 'nullable|date' : 'required|date',
            'end_datetime'   => $asDraft ? 'nullable|date' : 'required|date|after:start_datetime',
            'description'    => 'nullable|string',
            'price'          => 'nullable|numeric|min:0',
            'capacity'       => 'nullable|integer|min:1',
            'age_restriction'=> 'nullable|integer|min:0|max:21',
            'venue_id'       => 'nullable|integer',
            'category_id'    => 'nullable|integer',
            'image'          => 'nullable|string',
            'gallery'        => 'nullable|array',
            'gallery.*'      => 'string|max:2000',
        ]);

        $statusId = $asDraft ? 5 : 4;
        $event = Event::create([...$data, 'organization_id' => $org->organization_id, 'status_id' => $statusId]);
        return $this->success($this->formatEvent($event->load(['organization', 'category', 'status', 'venue'])), 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $event = Event::find($id);
        if (!$event) return $this->error('Событие не найдено', 404);

        $user = $request->user();
        if (!($user instanceof \App\Models\Organization) || $event->organization_id !== $user->organization_id) {
            return $this->error('Нет доступа', 403);
        }

        $allowed = $request->only(['title', 'description', 'start_datetime', 'end_datetime',
                                   'price', 'capacity', 'age_restriction', 'image', 'gallery', 'venue_id', 'category_id']);
        if ($request->boolean('as_draft')) {
            $allowed['status_id'] = 5;
        } elseif ($event->status_id == 5) {
            $allowed['status_id'] = 4;
        }
        $event->fill($allowed)->save();
        return $this->success($this->formatEvent($event->load(['organization', 'category', 'status', 'venue'])));
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $event = Event::find($id);
        if (!$event) return $this->error('Событие не найдено', 404);

        $user = $request->user();
        if (!($user instanceof \App\Models\Organization) || $event->organization_id !== $user->organization_id) {
            return $this->error('Нет доступа', 403);
        }

        // Удаляем связанные отзывы и тикеты перед удалением события
        $event->reviews()->delete();
        $event->tickets()->delete();
        $event->delete();
        return $this->success(null, 200, 'Удалено');
    }

    public function stats(Request $request): JsonResponse
    {
        $org = $request->user();
        if (!($org instanceof \App\Models\Organization)) {
            return $this->error('Нет доступа', 403);
        }

        $eventIds = Event::where('organization_id', $org->organization_id)->pluck('event_id');

        $totalEvents  = $eventIds->count();
        $activeEvents = Event::where('organization_id', $org->organization_id)->where('status_id', 1)->count();
        $draftEvents  = Event::where('organization_id', $org->organization_id)->where('status_id', 5)->count();

        $ticketRows = Ticket::whereIn('event_id', $eventIds)
            ->whereIn('status', ['paid', 'return_pending'])
            ->selectRaw('event_id, COUNT(*) as qty, SUM(price * quantity) as revenue, MAX(paid_at) as last_sale')
            ->groupBy('event_id')
            ->get();

        $totalTickets = $ticketRows->sum('qty');
        $totalRevenue = $ticketRows->sum('revenue');

        $byEvent = Event::whereIn('event_id', $ticketRows->pluck('event_id'))
            ->select('event_id', 'title')
            ->get()
            ->keyBy('event_id');

        $topEvents = $ticketRows->sortByDesc('revenue')->take(5)->map(fn($r) => [
            'event_id' => $r->event_id,
            'title'    => $byEvent[$r->event_id]?->title ?? '—',
            'qty'      => (int)$r->qty,
            'revenue'  => (float)$r->revenue,
        ])->values();

        return $this->success([
            'total_events'  => $totalEvents,
            'active_events' => $activeEvents,
            'draft_events'  => $draftEvents,
            'total_tickets' => (int)$totalTickets,
            'total_revenue' => (float)$totalRevenue,
            'top_events'    => $topEvents,
        ]);
    }

    private function formatEvent(Event $e): array
    {
        $ticketsSold = $e->capacity !== null
            ? (int) Ticket::where('event_id', $e->event_id)->whereIn('status', ['paid', 'return_pending'])->sum('quantity')
            : null;

        return [
            'event_id'          => $e->event_id,
            'title'             => $e->title,
            'description'       => $e->description,
            'start_datetime'    => $e->start_datetime,
            'end_datetime'      => $e->end_datetime,
            'price'             => $e->price,
            'capacity'          => $e->capacity,
            'tickets_sold'      => $ticketsSold,
            'tickets_left'      => $e->capacity !== null ? max(0, $e->capacity - $ticketsSold) : null,
            'age_restriction'   => $e->age_restriction,
            'image'             => $e->image,
            'gallery'           => $e->gallery ?? [],
            'organization_id'   => $e->organization_id,
            'venue_id'          => $e->venue_id,
            'category_id'       => $e->category_id,
            'status_id'         => $e->status_id,
            'category_name'     => $e->category?->name,
            'status_name'       => $e->status?->status_name,
            'organization_name' => $e->organization?->full_name,
            'org_status_id'     => $e->organization?->status_id,
            'venue_name'        => $e->venue?->name,
            'venue_address'     => $e->venue?->address,
        ];
    }
}

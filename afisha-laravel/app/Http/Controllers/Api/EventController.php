<?php

namespace App\Http\Controllers\Api;

use App\Enums\OrgStatus;
use App\Models\Event;
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
        if ($request->organization_id) $q->where('organization_id', $request->organization_id);
        if ($request->status_id) $q->where('events.status_id', $request->status_id);
        if ($request->date_from) $q->where('start_datetime', '>=', $request->date_from);
        if ($request->date_to)   $q->where('start_datetime', '<=', $request->date_to);
        if ($request->free)      $q->where('price', 0);
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

        if ($request->limit) $q->limit($request->limit)->offset($request->offset ?? 0);

        $events = $q->get()->map(fn($e) => $this->formatEvent($e));
        return $this->success($events);
    }

    public function show(int $id): JsonResponse
    {
        $event = Event::with(['organization', 'category', 'status', 'venue'])->find($id);
        if (!$event) return $this->error('Событие не найдено', 404);
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

        $data = $request->validate([
            'title'          => 'required|string|max:200',
            'start_datetime' => 'required|date',
            'end_datetime'   => 'required|date|after:start_datetime',
            'description'    => 'nullable|string',
            'price'          => 'nullable|numeric|min:0',
            'age_restriction'=> 'nullable|integer|min:0|max:21',
            'venue_id'       => 'nullable|integer',
            'category_id'    => 'nullable|integer',
            'image'          => 'nullable|string',
        ]);

        $event = Event::create([...$data, 'organization_id' => $org->organization_id, 'status_id' => 4]);
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
                                   'price', 'age_restriction', 'image', 'venue_id', 'category_id']);
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

    private function formatEvent(Event $e): array
    {
        return [
            'event_id'          => $e->event_id,
            'title'             => $e->title,
            'description'       => $e->description,
            'start_datetime'    => $e->start_datetime,
            'end_datetime'      => $e->end_datetime,
            'price'             => $e->price,
            'age_restriction'   => $e->age_restriction,
            'image'             => $e->image,
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

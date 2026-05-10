<?php

namespace App\Http\Controllers\Api;

use App\Models\Venue;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VenueController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $q = Venue::with(['category', 'schedule', 'contacts']);
        if ($request->search) {
            $term = $request->search;
            $q->where(function ($sub) use ($term) {
                $sub->whereRaw('MATCH(name, description, address) AGAINST(? IN BOOLEAN MODE)', [$term.'*'])
                    ->orWhere('name', 'like', '%'.$term.'%');
            });
        }
        $venues = $q->get()->map(fn($v) => $this->formatVenue($v));
        return $this->success($venues);
    }

    public function show(int $id): JsonResponse
    {
        $venue = Venue::with(['category', 'schedule', 'contacts'])->find($id);
        if (!$venue) return $this->error('Площадка не найдена', 404);
        return $this->success($this->formatVenue($venue));
    }

    public function store(Request $request): JsonResponse
    {
        if (!($request->user() instanceof \App\Models\Organization)) {
            return $this->error('Только организации могут создавать площадки', 403);
        }
        $data = $request->validate([
            'name'        => 'required|string|max:200',
            'description' => 'nullable|string',
            'address'     => 'nullable|string',
            'image'       => 'nullable|string',
            'age'         => 'nullable|integer',
            'category_id' => 'nullable|integer',
        ]);
        $venue = Venue::create($data);
        return $this->success($this->formatVenue($venue->load(['category', 'schedule', 'contacts'])), 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        if (!($request->user() instanceof \App\Models\Organization)) {
            return $this->error('Нет доступа', 403);
        }
        $venue = Venue::find($id);
        if (!$venue) return $this->error('Площадка не найдена', 404);
        $allowed = $request->only(['name', 'description', 'address', 'image', 'age', 'category_id']);
        $venue->fill($allowed)->save();
        return $this->success($this->formatVenue($venue->load(['category', 'schedule', 'contacts'])));
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        if (!($request->user() instanceof \App\Models\Organization)) {
            return $this->error('Нет доступа', 403);
        }
        $venue = Venue::find($id);
        if (!$venue) return $this->error('Площадка не найдена', 404);
        // Удаляем связанные контакты и расписание перед удалением площадки
        $venue->contacts()->delete();
        $venue->schedule()->delete();
        $venue->delete();
        return $this->success(null, 200, 'Удалено');
    }

    private function formatVenue(Venue $v): array
    {
        return [
            'venue_id'      => $v->venue_id,
            'name'          => $v->name,
            'description'   => $v->description,
            'address'       => $v->address,
            'image'         => $v->image,
            'age'           => $v->age,
            'category_id'   => $v->category_id,
            'category_name' => $v->category?->name,
            'schedule'      => $v->schedule,
            'contacts'      => $v->contacts,
        ];
    }
}

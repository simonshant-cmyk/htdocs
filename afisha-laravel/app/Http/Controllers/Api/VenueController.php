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
        if ($request->organization_id) {
            $q->where('organization_id', $request->organization_id);
        }
        if ($request->category_id) {
            $q->where('category_id', $request->category_id);
        }
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
        $org = $request->user();
        if (!($org instanceof \App\Models\Organization)) {
            return $this->error('Только организации могут создавать площадки', 403);
        }
        $data = $request->validate([
            'name'        => 'required|string|max:200',
            'description' => 'nullable|string',
            'address'     => 'nullable|string',
            'image'       => 'nullable|string',
            'gallery'     => 'nullable|array',
            'gallery.*'   => 'string|max:2000',
            'age'         => 'nullable|integer',
            'category_id' => 'nullable|integer',
            'latitude'    => 'nullable|numeric|between:-90,90',
            'longitude'   => 'nullable|numeric|between:-180,180',
        ]);

        $venue = Venue::create([...$data, 'organization_id' => $org->organization_id]);
        return $this->success($this->formatVenue($venue->load(['category', 'schedule', 'contacts'])), 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $org = $request->user();
        if (!($org instanceof \App\Models\Organization)) {
            return $this->error('Нет доступа', 403);
        }
        $venue = Venue::find($id);
        if (!$venue) return $this->error('Площадка не найдена', 404);

        // Allow update if owner OR venue has no owner yet (legacy data)
        if ($venue->organization_id && $venue->organization_id !== $org->organization_id) {
            return $this->error('Нет доступа — площадка принадлежит другой организации', 403);
        }

        $allowed = $request->only([
            'name', 'description', 'address', 'image', 'gallery',
            'age', 'category_id', 'latitude', 'longitude',
        ]);
        // Claim ownership for legacy venues on first edit
        if (!$venue->organization_id) {
            $allowed['organization_id'] = $org->organization_id;
        }
        $venue->fill($allowed)->save();
        return $this->success($this->formatVenue($venue->load(['category', 'schedule', 'contacts'])));
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $org = $request->user();
        if (!($org instanceof \App\Models\Organization)) {
            return $this->error('Нет доступа', 403);
        }
        $venue = Venue::find($id);
        if (!$venue) return $this->error('Площадка не найдена', 404);

        if ($venue->organization_id && $venue->organization_id !== $org->organization_id) {
            return $this->error('Нет доступа — площадка принадлежит другой организации', 403);
        }

        $venue->contacts()->delete();
        $venue->schedule()->delete();
        $venue->delete();
        return $this->success(null, 200, 'Удалено');
    }

    private function formatVenue(Venue $v): array
    {
        return [
            'venue_id'          => $v->venue_id,
            'name'              => $v->name,
            'description'       => $v->description,
            'address'           => $v->address,
            'image'             => $v->image,
            'gallery'           => $v->gallery ?? [],
            'age'               => $v->age,
            'category_id'       => $v->category_id,
            'category_name'     => $v->category?->name,
            'organization_id'   => $v->organization_id,
            'latitude'          => $v->latitude,
            'longitude'         => $v->longitude,
            'schedule'          => $v->schedule,
            'contacts'          => $v->contacts,
        ];
    }
}

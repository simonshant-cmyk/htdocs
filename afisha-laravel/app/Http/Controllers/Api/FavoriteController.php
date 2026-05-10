<?php

namespace App\Http\Controllers\Api;

use App\Models\Favorite;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FavoriteController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $favorites = Favorite::with(['event.category', 'venue'])
            ->where('user_id', $request->user()->user_id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($f) => [
                'favorite_id'    => $f->favorite_id,
                'event_id'       => $f->event_id,
                'venue_id'       => $f->venue_id,
                'created_at'     => $f->created_at,
                'event_title'    => $f->event?->title,
                'start_datetime' => $f->event?->start_datetime,
                'event_price'    => $f->event?->price,
                'event_image'    => $f->event?->image,
                'event_category' => $f->event?->category?->name,
                'venue_name'     => $f->venue?->name,
                'venue_address'  => $f->venue?->address,
                'venue_image'    => $f->venue?->image,
            ]);
        return $this->success($favorites);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validate([
            'event_id' => 'nullable|integer',
            'venue_id' => 'nullable|integer',
        ]);

        $exists = Favorite::where('user_id', $user->user_id)
            ->where('event_id', $data['event_id'] ?? null)
            ->where('venue_id', $data['venue_id'] ?? null)
            ->exists();

        if ($exists) return $this->error('Уже в избранном');

        $fav = Favorite::create([...$data, 'user_id' => $user->user_id, 'created_at' => now()]);
        return $this->success($fav, 201);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        Favorite::where('favorite_id', $id)->where('user_id', $request->user()->user_id)->delete();
        return $this->success(null, 200, 'Удалено из избранного');
    }
}

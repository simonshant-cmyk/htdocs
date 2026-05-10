<?php

namespace App\Http\Controllers\Api;

use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $q = Review::with('user')->orderBy('created_at', 'desc');
        if ($request->event_id) $q->where('event_id', $request->event_id);
        if ($request->venue_id) $q->where('venue_id', $request->venue_id);

        $reviews = $q->get()->map(fn($r) => [
            'review_id'   => $r->review_id,
            'user_id'     => $r->user_id,
            'text'        => $r->text,
            'rating'      => $r->rating,
            'created_at'  => $r->created_at,
            'event_id'    => $r->event_id,
            'venue_id'    => $r->venue_id,
            'user_name'   => $r->user?->full_name,
            'user_avatar' => $r->user?->avatar,
        ]);
        return $this->success($reviews);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!($user instanceof \App\Models\User)) {
            return $this->error('Только пользователи могут оставлять отзывы', 403);
        }
        if (in_array($user->status, ['blocked', 'restricted'])) {
            return $this->error('Вам ограничена возможность оставлять отзывы', 403);
        }

        $data = $request->validate([
            'text'     => 'required|string',
            'rating'   => 'required|integer|min:1|max:5',
            'event_id' => 'nullable|integer',
            'venue_id' => 'nullable|integer',
        ]);

        $review = Review::create([...$data, 'user_id' => $user->user_id, 'created_at' => now()]);
        return $this->success($review->load('user'), 201);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $review = Review::find($id);
        if (!$review) return $this->error('Отзыв не найден', 404);
        if ($review->user_id !== $request->user()->user_id) return $this->error('Нет доступа', 403);
        $review->delete();
        return $this->success(null, 200, 'Отзыв удалён');
    }
}

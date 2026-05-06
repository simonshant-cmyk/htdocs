<?php
require_once __DIR__ . '/../models/ReviewFavoriteModel.php';
require_once __DIR__ . '/../helpers/Auth.php';
require_once __DIR__ . '/../helpers/Response.php';

class ReviewController {

    // GET /api/reviews?event_id=1  или  ?venue_id=1
    public function index(): void {
        $model = new ReviewModel();
        if (!empty($_GET['event_id'])) {
            Response::success($model->getByEvent((int)$_GET['event_id']));
        } elseif (!empty($_GET['venue_id'])) {
            Response::success($model->getByVenue((int)$_GET['venue_id']));
        } else {
            Response::error('Укажите event_id или venue_id');
        }
    }

    // DELETE /api/reviews/{id}
    public function delete(int $id): void {
        $payload = Auth::require();
        if (($payload['type'] ?? '') !== 'user') {
            Response::error('Только пользователи могут удалять отзывы', 403);
        }
        $model = new ReviewModel();
        $review = $model->findById($id);
        if (!$review) Response::notFound('Отзыв не найден');
        if ($review['user_id'] !== $payload['user_id']) Response::error('Нет доступа', 403);
        $model->delete($payload['user_id'], $id);
        Response::success(null, 'Отзыв удалён');
    }

    // POST /api/reviews
    public function create(): void {
        $payload = Auth::require();
        $data = json_decode(file_get_contents('php://input'), true);

        if (empty($data['text']) || empty($data['rating'])) {
            Response::error('Поля text и rating обязательны');
        }
        if ($data['rating'] < 1 || $data['rating'] > 5) {
            Response::error('Rating должен быть от 1 до 5');
        }

        $data['user_id'] = $payload['user_id'];
        $id = (new ReviewModel())->create($data);
        Response::success(['review_id' => $id], 'Отзыв добавлен', 201);
    }
}

class FavoriteController {

    // GET /api/favorites
    public function index(): void {
        $payload = Auth::require();
        $favorites = (new FavoriteModel())->getByUser($payload['user_id']);
        Response::success($favorites);
    }

    // POST /api/favorites
    public function add(): void {
        $payload = Auth::require();
        $data = json_decode(file_get_contents('php://input'), true);

        $eventId = $data['event_id'] ?? null;
        $venueId = $data['venue_id'] ?? null;

        if (!$eventId && !$venueId) {
            Response::error('Укажите event_id или venue_id');
        }

        $model = new FavoriteModel();
        if ($model->exists($payload['user_id'], $eventId, $venueId)) {
            Response::error('Уже в избранном');
        }

        $id = $model->add($payload['user_id'], $eventId, $venueId);
        Response::success(['favorite_id' => $id], 'Добавлено в избранное', 201);
    }

    // DELETE /api/favorites/{id}
    public function remove(int $id): void {
        $payload = Auth::require();
        $ok = (new FavoriteModel())->remove($payload['user_id'], $id);
        if (!$ok) Response::notFound('Не найдено');
        Response::success(null, 'Удалено из избранного');
    }
}

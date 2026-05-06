<?php
require_once __DIR__ . '/../models/EventModel.php';
require_once __DIR__ . '/../helpers/Auth.php';
require_once __DIR__ . '/../helpers/Response.php';

class EventController {

    // GET /api/events
    public function index(): void {
        $filters = array_filter([
            'category_id'     => $_GET['category_id'] ?? null,
            'organization_id' => $_GET['organization_id'] ?? null,
            'status_id'       => $_GET['status_id'] ?? null,
            'date_from'       => $_GET['date_from'] ?? null,
            'search'          => $_GET['search'] ?? null,
            'sort'            => $_GET['sort'] ?? null,
        ]);
        if (isset($_GET['limit'])) {
            $filters['limit']  = (int) $_GET['limit'];
            $filters['offset'] = (int) ($_GET['offset'] ?? 0);
        }
        $result = (new EventModel())->getAll($filters);
        Response::success($result);
    }

    // GET /api/events/{id}
    public function show(int $id): void {
        $event = (new EventModel())->findById($id);
        if (!$event) Response::notFound('Событие не найдено');
        Response::success($event);
    }

    // POST /api/events
    public function create(): void {
        $payload = Auth::require();
        if (($payload['type'] ?? '') !== 'organization') {
            Response::error('Только организации могут создавать события', 403);
        }

        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['title']) || empty($data['start_datetime']) || empty($data['end_datetime'])) {
            Response::error('Поля title, start_datetime, end_datetime обязательны');
        }

        $data['organization_id'] = $payload['org_id'];
        $id = (new EventModel())->create($data);
        $event = (new EventModel())->findById($id);
        Response::success($event, 'Событие создано', 201);
    }

    // PUT /api/events/{id}
    public function update(int $id): void {
        $payload = Auth::require();
        $data = json_decode(file_get_contents('php://input'), true);
        $model = new EventModel();

        $event = $model->findById($id);
        if (!$event) Response::notFound('Событие не найдено');

        // Организация может редактировать только свои события
        if ($payload['type'] === 'organization' && $event['organization_id'] !== $payload['org_id']) {
            Response::error('Нет доступа', 403);
        }

        $model->update($id, $data);
        Response::success($model->findById($id));
    }

    // DELETE /api/events/{id}
    public function delete(int $id): void {
        $payload = Auth::require();
        $model = new EventModel();
        $event = $model->findById($id);
        if (!$event) Response::notFound();

        if ($payload['type'] === 'organization' && $event['organization_id'] !== $payload['org_id']) {
            Response::error('Нет доступа', 403);
        }

        $model->delete($id);
        Response::success(null, 'Удалено');
    }
}


require_once __DIR__ . '/../models/VenueModel.php';

class VenueController {

    // GET /api/venues
    public function index(): void {
        $filters = [
            'category_id' => $_GET['category_id'] ?? null,
            'search'      => $_GET['search'] ?? null,
        ];
        $venues = (new VenueModel())->getAll(array_filter($filters));
        Response::success($venues);
    }

    // GET /api/venues/{id}
    public function show(int $id): void {
        $venue = (new VenueModel())->findById($id);
        if (!$venue) Response::notFound('Площадка не найдена');
        Response::success($venue);
    }

    // POST /api/venues
    public function create(): void {
        Auth::require();
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['name'])) {
            Response::error('Поле name обязательно');
        }
        $id = (new VenueModel())->create($data);
        $venue = (new VenueModel())->findById($id);
        Response::success($venue, 'Площадка создана', 201);
    }

    // PUT /api/venues/{id}
    public function update(int $id): void {
        $payload = Auth::require();
        if (($payload['type'] ?? '') !== 'organization') {
            Response::error('Только организации могут редактировать площадки', 403);
        }
        $model = new VenueModel();
        if (!$model->findById($id)) Response::notFound('Площадка не найдена');
        $data = json_decode(file_get_contents('php://input'), true);
        $model->update($id, $data);
        Response::success($model->findById($id));
    }

    // DELETE /api/venues/{id}
    public function delete(int $id): void {
        $payload = Auth::require();
        if (($payload['type'] ?? '') !== 'organization') {
            Response::error('Только организации могут удалять площадки', 403);
        }
        $model = new VenueModel();
        if (!$model->findById($id)) Response::notFound('Площадка не найдена');
        $model->delete($id);
        Response::success(null, 'Площадка удалена');
    }
}

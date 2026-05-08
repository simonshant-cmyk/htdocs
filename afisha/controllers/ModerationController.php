<?php
require_once __DIR__ . '/../models/ModerationModel.php';
require_once __DIR__ . '/../helpers/Auth.php';
require_once __DIR__ . '/../helpers/Response.php';

class ModerationController {

    private function requireModerator(): array {
        $payload = Auth::require();
        if (($payload['type'] ?? '') !== 'user') {
            Response::error('Доступ запрещён', 403);
        }
        if (!in_array((int)($payload['role_id'] ?? 0), [1, 3])) {
            Response::error('Недостаточно прав. Требуется роль модератора.', 403);
        }
        return $payload;
    }

    // GET /api/moderation/stats
    public function stats(): void {
        $this->requireModerator();
        Response::success((new ModerationModel())->getStats());
    }

    // GET /api/moderation/orgs?filter=pending|all
    public function organizations(): void {
        $this->requireModerator();
        $pendingOnly = ($_GET['filter'] ?? 'pending') !== 'all';
        Response::success((new ModerationModel())->getOrganizations($pendingOnly));
    }

    // PUT /api/moderation/orgs/:id  { "status_id": 1|3 }
    public function updateOrg(int $id): void {
        $this->requireModerator();
        $data = json_decode(file_get_contents('php://input'), true) ?? [];
        $statusId = (int)($data['status_id'] ?? 0);
        if (!in_array($statusId, [1, 3])) {
            Response::error('Допустимые статусы: 1 (одобрить) или 3 (отклонить)');
        }
        if ($statusId === 3 && empty($data['rejection_reason'])) {
            Response::error('Укажите причину отклонения');
        }
        $reason = isset($data['rejection_reason']) ? trim($data['rejection_reason']) : null;
        (new ModerationModel())->setOrgStatus($id, $statusId, $reason);
        $msg = $statusId === 1 ? 'Организация одобрена' : 'Организация отклонена';
        Response::success(null, $msg);
    }

    // GET /api/moderation/reviews
    public function reviews(): void {
        $this->requireModerator();
        Response::success((new ModerationModel())->getReviews());
    }

    // DELETE /api/moderation/reviews/:id
    public function deleteReview(int $id): void {
        $this->requireModerator();
        (new ModerationModel())->deleteReview($id);
        Response::success(null, 'Отзыв удалён');
    }

    // GET /api/moderation/events
    public function events(): void {
        $this->requireModerator();
        Response::success((new ModerationModel())->getEvents());
    }

    // PUT /api/moderation/events/:id  { "status_id": 1|2|3 }
    public function updateEvent(int $id): void {
        $this->requireModerator();
        $data = json_decode(file_get_contents('php://input'), true) ?? [];
        $statusId = (int)($data['status_id'] ?? 0);
        if (!in_array($statusId, [1, 2, 3, 4])) {
            Response::error('Недопустимый статус');
        }
        (new ModerationModel())->setEventStatus($id, $statusId);
        Response::success(null, 'Статус события обновлён');
    }

    // GET /api/moderation/users
    public function users(): void {
        $this->requireModerator();
        Response::success((new ModerationModel())->getUsers());
    }

    // PUT /api/moderation/users/:id  { "role_id": 2|3 }
    public function updateUser(int $id): void {
        $payload = $this->requireModerator();
        $data   = json_decode(file_get_contents('php://input'), true) ?? [];
        $roleId = (int)($data['role_id'] ?? 0);
        if (!in_array($roleId, [2, 3])) {
            Response::error('Допустимые роли: 2 (пользователь) или 3 (модератор)');
        }
        if ($id === (int)($payload['user_id'] ?? 0)) {
            Response::error('Нельзя изменить роль самому себе', 403);
        }
        (new ModerationModel())->setUserRole($id, $roleId);
        Response::success(null, 'Роль обновлена');
    }
}

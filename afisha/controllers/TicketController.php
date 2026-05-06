<?php
require_once __DIR__ . '/../models/TicketModel.php';
require_once __DIR__ . '/../models/EventModel.php';
require_once __DIR__ . '/../helpers/Auth.php';
require_once __DIR__ . '/../helpers/Response.php';

class TicketController {

    // GET /api/tickets — корзина текущего пользователя
    public function cart(): void {
        $payload = Auth::require();
        if ($payload['type'] !== 'user') Response::error('Только пользователи', 403);
        $items = (new TicketModel())->getCart($payload['user_id']);
        Response::success($items);
    }

    // GET /api/tickets/paid — оплаченные билеты
    public function paid(): void {
        $payload = Auth::require();
        if ($payload['type'] !== 'user') Response::error('Только пользователи', 403);
        $items = (new TicketModel())->getPaid($payload['user_id']);
        Response::success($items);
    }

    // GET /api/tickets/count — кол-во в корзине
    public function count(): void {
        $payload = Auth::require();
        if ($payload['type'] !== 'user') Response::error('Только пользователи', 403);
        $cnt = (new TicketModel())->cartCount($payload['user_id']);
        Response::success(['count' => $cnt]);
    }

    // POST /api/tickets — добавить в корзину
    public function add(): void {
        $payload = Auth::require();
        if ($payload['type'] !== 'user') Response::error('Только пользователи могут покупать билеты', 403);

        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['event_id'])) Response::error('Укажите event_id');

        $qty = max(1, (int)($data['quantity'] ?? 1));
        $event = (new EventModel())->findById((int)$data['event_id']);
        if (!$event) Response::notFound('Событие не найдено');

        $id = (new TicketModel())->add($payload['user_id'], $event['event_id'], (float)$event['price'], $qty);
        Response::success(['ticket_id' => $id], 'Добавлено в корзину', 201);
    }

    // PUT /api/tickets/{id} — изменить количество
    public function update(int $id): void {
        $payload = Auth::require();
        if ($payload['type'] !== 'user') Response::error('Только пользователи', 403);
        $data = json_decode(file_get_contents('php://input'), true);
        $qty = (int)($data['quantity'] ?? 1);
        (new TicketModel())->updateQty($id, $payload['user_id'], $qty);
        Response::success(null, 'Обновлено');
    }

    // DELETE /api/tickets/{id} — удалить из корзины
    public function remove(int $id): void {
        $payload = Auth::require();
        if ($payload['type'] !== 'user') Response::error('Только пользователи', 403);
        $ok = (new TicketModel())->remove($id, $payload['user_id']);
        if (!$ok) Response::notFound('Не найдено');
        Response::success(null, 'Удалено');
    }

    // POST /api/tickets/checkout — оформить заказ
    public function checkout(): void {
        $payload = Auth::require();
        if ($payload['type'] !== 'user') Response::error('Только пользователи', 403);
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['payment_method'])) Response::error('Укажите способ оплаты');

        $count = (new TicketModel())->checkout($payload['user_id'], $data['payment_method']);
        if ($count === 0) Response::error('Корзина пуста');
        Response::success(['paid' => $count], 'Оплата прошла успешно');
    }
}

<?php
// ── CORS ──
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

header('Content-Type: application/json; charset=utf-8');

// ── Autoload controllers / helpers ──
require_once __DIR__ . '/helpers/Response.php';
require_once __DIR__ . '/helpers/Auth.php';
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/EventVenueController.php';
require_once __DIR__ . '/controllers/ReviewFavoriteController.php';
require_once __DIR__ . '/models/CategoryStatusModel.php';
require_once __DIR__ . '/controllers/UploadController.php';
require_once __DIR__ . '/controllers/TicketController.php';
require_once __DIR__ . '/controllers/ModerationController.php';
require_once __DIR__ . '/controllers/AnalyticsController.php';

// ── Роутер ──
$method = $_SERVER['REQUEST_METHOD'];
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri    = rtrim(preg_replace('#^/afisha/api#', '', $uri), '/');
$parts  = explode('/', ltrim($uri, '/'));

$resource = $parts[0] ?? '';
$p1       = $parts[1] ?? null;
$p2       = $parts[2] ?? null;
$p3       = $parts[3] ?? null;
$id       = isset($parts[1]) && is_numeric($parts[1]) ? (int)$parts[1] : null;
$sub_id   = isset($parts[2]) && is_numeric($parts[2]) ? (int)$parts[2] : null;

try {
    match (true) {

        // ── AUTH ──
        $resource === 'auth' && $p1 === 'register'                        && $method === 'POST' => (new AuthController())->register(),
        $resource === 'auth' && $p1 === 'login'                           && $method === 'POST' => (new AuthController())->login(),
        $resource === 'auth' && $p1 === 'org' && $p2 === 'register'       && $method === 'POST' => (new AuthController())->orgRegister(),
        $resource === 'auth' && $p1 === 'org' && $p2 === 'login'          && $method === 'POST' => (new AuthController())->orgLogin(),
        $resource === 'auth' && $p1 === 'me'                              && $method === 'GET'  => (new AuthController())->me(),
        $resource === 'auth' && $p1 === 'me'                              && $method === 'PUT'  => (new AuthController())->updateMe(),

        // ── EVENTS ──
        $resource === 'events' && $id === null  && $method === 'GET'    => (new EventController())->index(),
        $resource === 'events' && $id !== null  && $method === 'GET'    => (new EventController())->show($id),
        $resource === 'events' && $id === null  && $method === 'POST'   => (new EventController())->create(),
        $resource === 'events' && $id !== null  && $method === 'PUT'    => (new EventController())->update($id),
        $resource === 'events' && $id !== null  && $method === 'DELETE' => (new EventController())->delete($id),

        // ── VENUES ──
        $resource === 'venues' && $id === null  && $method === 'GET'    => (new VenueController())->index(),
        $resource === 'venues' && $id !== null  && $method === 'GET'    => (new VenueController())->show($id),
        $resource === 'venues' && $id === null  && $method === 'POST'   => (new VenueController())->create(),
        $resource === 'venues' && $id !== null  && $method === 'PUT'    => (new VenueController())->update($id),
        $resource === 'venues' && $id !== null  && $method === 'DELETE' => (new VenueController())->delete($id),

        // ── REVIEWS ──
        $resource === 'reviews' && $id === null && $method === 'GET'    => (new ReviewController())->index(),
        $resource === 'reviews' && $id === null && $method === 'POST'   => (new ReviewController())->create(),
        $resource === 'reviews' && $id !== null && $method === 'DELETE' => (new ReviewController())->delete($id),

        // ── FAVORITES ──
        $resource === 'favorites' && $id === null && $method === 'GET'    => (new FavoriteController())->index(),
        $resource === 'favorites' && $id === null && $method === 'POST'   => (new FavoriteController())->add(),
        $resource === 'favorites' && $id !== null && $method === 'DELETE' => (new FavoriteController())->remove($id),

        // ── TICKETS / CART ──
        $resource === 'tickets' && $p1 === 'checkout'  && $method === 'POST'   => (new TicketController())->checkout(),
        $resource === 'tickets' && $p1 === 'count'     && $method === 'GET'    => (new TicketController())->count(),
        $resource === 'tickets' && $p1 === 'paid'      && $method === 'GET'    => (new TicketController())->paid(),
        $resource === 'tickets' && $id === null        && $method === 'GET'    => (new TicketController())->cart(),
        $resource === 'tickets' && $id === null        && $method === 'POST'   => (new TicketController())->add(),
        $resource === 'tickets' && $id !== null        && $method === 'PUT'    => (new TicketController())->update($id),
        $resource === 'tickets' && $id !== null        && $method === 'DELETE' => (new TicketController())->remove($id),

        // ── MODERATION ──
        $resource === 'moderation' && $p1 === 'stats'    && $method === 'GET'                             => (new ModerationController())->stats(),
        $resource === 'moderation' && $p1 === 'orgs'     && $sub_id === null && $method === 'GET'         => (new ModerationController())->organizations(),
        $resource === 'moderation' && $p1 === 'orgs'     && $sub_id !== null && $method === 'PUT'         => (new ModerationController())->updateOrg($sub_id),
        $resource === 'moderation' && $p1 === 'reviews'  && $sub_id === null && $method === 'GET'         => (new ModerationController())->reviews(),
        $resource === 'moderation' && $p1 === 'reviews'  && $sub_id !== null && $method === 'DELETE'      => (new ModerationController())->deleteReview($sub_id),
        $resource === 'moderation' && $p1 === 'events'   && $sub_id === null && $method === 'GET'         => (new ModerationController())->events(),
        $resource === 'moderation' && $p1 === 'events'   && $sub_id !== null && $method === 'PUT'         => (new ModerationController())->updateEvent($sub_id),
        $resource === 'moderation' && $p1 === 'users'    && $sub_id === null && $method === 'GET'         => (new ModerationController())->users(),
        $resource === 'moderation' && $p1 === 'users'    && $sub_id !== null && $method === 'PUT'         => (new ModerationController())->updateUser($sub_id),

        // ── CATEGORIES ──
        $resource === 'categories' && $method === 'GET' => Response::success((new CategoryModel())->getAll()),

        // ── STATUSES ──
        $resource === 'statuses' && $method === 'GET' => Response::success((new StatusModel())->getAll()),

        // ── UPLOAD ──
        $resource === 'upload' && $p1 === 'avatar' && $method === 'POST' => (new UploadController())->avatar(),
        $resource === 'upload' && $p1 === 'event'  && $method === 'POST' => (new UploadController())->event(),
        $resource === 'upload' && $p1 === 'venue'  && $method === 'POST' => (new UploadController())->venue(),

        // ── ANALYTICS ──
        $resource === 'analytics' && $p1 === 'org' && $method === 'GET' => (new AnalyticsController())->orgStats(),

        default => Response::notFound("Маршрут не найден: $method /api/$resource")
    };
} catch (Throwable $e) {
    Response::error('Внутренняя ошибка: ' . $e->getMessage(), 500);
}

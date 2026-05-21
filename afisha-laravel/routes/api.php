<?php

use App\Http\Controllers\Api\{
    AuthController,
    EventController,
    VenueController,
    ReviewController,
    FavoriteController,
    TicketController,
    ModerationController,
    UploadController,
    AnalyticsController,
    CategoryController,
    SubscriptionController,
    PromoController,
};
use Illuminate\Support\Facades\Route;

// ── AUTH ──  (5 попыток в минуту на один IP)
Route::middleware('throttle:5,1')->group(function () {
    Route::post('auth/register',        [AuthController::class, 'register']);
    Route::post('auth/login',           [AuthController::class, 'login']);
    Route::post('auth/org/register',    [AuthController::class, 'orgRegister']);
    Route::post('auth/org/login',       [AuthController::class, 'orgLogin']);
    Route::post('auth/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('auth/reset-password',  [AuthController::class, 'resetPassword']);
});
Route::middleware('auth:sanctum')->group(function () {
    Route::get('auth/me',              [AuthController::class, 'me']);
    Route::put('auth/me',              [AuthController::class, 'updateMe']);
    Route::post('auth/change-password',[AuthController::class, 'changePassword']);
});

// ── ORGS (public) ──
Route::get('orgs',      [AuthController::class, 'listOrgs']);
Route::get('orgs/{id}', [AuthController::class, 'showOrg']);

// ── EVENTS ──
Route::get('events',       [EventController::class, 'index']);
Route::middleware('auth:sanctum')->get('events/stats', [EventController::class, 'stats']); // must precede events/{id}
Route::get('events/{id}',  [EventController::class, 'show']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('events',           [EventController::class, 'store']);
    Route::put('events/{id}',       [EventController::class, 'update']);
    Route::delete('events/{id}',    [EventController::class, 'destroy']);
});

// ── VENUES ──
Route::get('venues',       [VenueController::class, 'index']);
Route::get('venues/{id}',  [VenueController::class, 'show']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('venues',        [VenueController::class, 'store']);
    Route::put('venues/{id}',    [VenueController::class, 'update']);
    Route::delete('venues/{id}', [VenueController::class, 'destroy']);
});

// ── REVIEWS ──
Route::get('reviews', [ReviewController::class, 'index']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('reviews',        [ReviewController::class, 'store']);
    Route::delete('reviews/{id}', [ReviewController::class, 'destroy']);
});

// ── FAVORITES ──
Route::middleware('auth:sanctum')->group(function () {
    Route::get('favorites',        [FavoriteController::class, 'index']);
    Route::post('favorites',       [FavoriteController::class, 'store']);
    Route::delete('favorites/{id}',[FavoriteController::class, 'destroy']);
});

// ── TICKETS ──
Route::middleware('auth:sanctum')->group(function () {
    Route::get('tickets',                  [TicketController::class, 'cart']);
    Route::post('tickets',                 [TicketController::class, 'add']);
    Route::post('tickets/checkout',        [TicketController::class, 'checkout']); // must precede tickets/{id}
    Route::get('tickets/count',            [TicketController::class, 'count']);
    Route::get('tickets/paid',             [TicketController::class, 'paid']);
    Route::put('tickets/{id}',             [TicketController::class, 'update']);
    Route::delete('tickets/{id}',          [TicketController::class, 'remove']);
    Route::post('tickets/{id}/return',     [TicketController::class, 'requestReturn']);
});

// ── MODERATION ──
Route::middleware('auth:sanctum')->prefix('moderation')->group(function () {
    Route::get('stats',                    [ModerationController::class, 'stats']);
    Route::get('orgs',                     [ModerationController::class, 'organizations']);
    Route::put('orgs/{id}',                [ModerationController::class, 'updateOrg']);
    Route::get('reviews',                  [ModerationController::class, 'reviews']);
    Route::delete('reviews/{id}',          [ModerationController::class, 'deleteReview']);
    Route::get('events',                   [ModerationController::class, 'events']);
    Route::put('events/{id}',              [ModerationController::class, 'updateEvent']);
    Route::get('users',                    [ModerationController::class, 'users']);
    Route::put('users/{id}',               [ModerationController::class, 'updateUser']);
    Route::post('users/{id}/warn',         [ModerationController::class, 'warnUser']);
    Route::post('users/{id}/block',        [ModerationController::class, 'blockUser']);
    Route::post('users/{id}/unblock',      [ModerationController::class, 'unblockUser']);
    Route::post('users/{id}/restrict',     [ModerationController::class, 'restrictUser']);
    Route::get('logs',                     [ModerationController::class, 'logs']);
    Route::get('returns',                  [ModerationController::class, 'returns']);
    Route::post('returns/{id}/approve',    [ModerationController::class, 'approveReturn']);
    Route::post('returns/{id}/reject',     [ModerationController::class, 'rejectReturn']);
});

// ── CATEGORIES / STATUSES ──
Route::get('categories', [CategoryController::class, 'index']);
Route::get('statuses',   [CategoryController::class, 'statuses']);

// ── UPLOAD ──
Route::middleware('auth:sanctum')->group(function () {
    Route::post('upload/avatar', [UploadController::class, 'avatar']);
    Route::post('upload/event',  [UploadController::class, 'event']);
    Route::post('upload/venue',  [UploadController::class, 'venue']);
});

// ── ANALYTICS ──
Route::middleware('auth:sanctum')->group(function () {
    Route::get('analytics/org',         [AnalyticsController::class, 'orgStats']);
    Route::get('analytics/org/buyers',  [AnalyticsController::class, 'orgBuyers']);
    Route::get('analytics/org/reviews', [AnalyticsController::class, 'orgReviews']);
});

// ── PROMO CODES ──
Route::middleware('auth:sanctum')->post('promo/validate', [PromoController::class, 'validate']);
Route::middleware('auth:sanctum')->group(function () {
    Route::get('promo',           [PromoController::class, 'index']);
    Route::post('promo',          [PromoController::class, 'store']);
    Route::delete('promo/{id}',   [PromoController::class, 'destroy']);
    Route::patch('promo/{id}',    [PromoController::class, 'toggle']);
});

// ── SUBSCRIPTIONS ──
Route::get('orgs/{id}/subscription',    [SubscriptionController::class, 'status']);
Route::middleware('auth:sanctum')->group(function () {
    Route::get('subscriptions',          [SubscriptionController::class, 'mySubscriptions']);
    Route::post('orgs/{id}/subscribe',   [SubscriptionController::class, 'subscribe']);
    Route::delete('orgs/{id}/subscribe', [SubscriptionController::class, 'unsubscribe']);
});

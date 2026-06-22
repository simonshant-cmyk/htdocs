<?php

use App\Http\Controllers\Auth\VkController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\WebController;
use Illuminate\Support\Facades\Route;

Route::get('/',           [WebController::class, 'index']);
Route::get('/event/{id}', [WebController::class, 'event'])->where('id', '[0-9]+');
Route::get('/venue/{id}', [WebController::class, 'venue'])->where('id', '[0-9]+');

Route::get('/login',          [WebController::class, 'login']);
Route::get('/register',       fn() => redirect('/login?register=1'));
Route::get('/forgot-password',[WebController::class, 'forgotPassword']);
Route::get('/reset-password', [WebController::class, 'resetPassword']);

Route::get('/auth/vk/redirect', [VkController::class, 'redirect']);
Route::get('/auth/vk/callback', [VkController::class, 'callback']);

Route::get('/cabinet',    [WebController::class, 'cabinet']);
Route::get('/org/cabinet',[WebController::class, 'orgCabinet']);
Route::get('/moderator',  [WebController::class, 'moderator']);

Route::get('/cart',           [WebController::class, 'cart']);
Route::get('/favorites',      [WebController::class, 'favorites']);
Route::get('/tickets',        [WebController::class, 'tickets']);
Route::get('/venues',         [WebController::class, 'venues']);
Route::get('/map',            [WebController::class, 'map']);
Route::get('/history',        [WebController::class, 'history']);
Route::get('/org/{id}',       [WebController::class, 'org'])->where('id', '[0-9]+');
Route::get('/category/{id}',  [WebController::class, 'category'])->where('id', '[0-9]+');
Route::get('/organizations',  [WebController::class, 'organizations']);
Route::get('/sitemap.xml',    [SitemapController::class, 'index']);

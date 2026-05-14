<?php

namespace App\Http\Controllers\Api;

use App\Models\{Category, Status};
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class CategoryController extends ApiController
{
    public function index(): JsonResponse
    {
        return $this->success(Cache::remember('categories', 3600, fn() => Category::all()->toArray()));
    }

    public function statuses(): JsonResponse
    {
        return $this->success(Cache::remember('statuses', 3600, fn() => Status::all()->toArray()));
    }
}

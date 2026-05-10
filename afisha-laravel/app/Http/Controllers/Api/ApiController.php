<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class ApiController extends Controller
{
    protected function success(mixed $data, int $status = 200, string $message = ''): JsonResponse
    {
        return response()->json(['data' => $data, 'message' => $message], $status);
    }

    protected function error(string $message, int $status = 422): JsonResponse
    {
        return response()->json(['error' => $message], $status);
    }
}

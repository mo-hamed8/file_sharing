<?php

namespace App\Http\Traits;

use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

trait ApiResponseTrait
{
    protected function success(mixed $data = null, string $message = '', int $status = 200): JsonResponse
    {
        return ApiResponse::success($data, $message, $status);
    }

    protected function error(string $message, int $status = 400, mixed $errors = null): JsonResponse
    {
        return ApiResponse::error($message, $status, $errors);
    }
}

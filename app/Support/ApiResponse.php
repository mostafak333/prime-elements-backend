<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    public static function success(mixed $data = null, string $message = 'success', int $code = 200): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => __($message),
            'data' => $data
        ], $code);
    }

    public static function error(string $message = 'error', int $code = 400, mixed $errors = null): JsonResponse
    {
        return response()->json([
            'status' => false,
            'message' => __($message),
            'errors' => $errors
        ], $code);
    }

    public static function created(mixed $data = null, string $message = 'created'): JsonResponse
    {
        return self::success($data, $message, 201);
    }
}

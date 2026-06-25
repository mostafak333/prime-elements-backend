<?php

namespace App\Traits;

trait ApiResponse
{
    protected function success(
        mixed $data = null,
        string $message = 'Success',
        int $code = 200
    ) {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    protected function error(
        string $message = 'Error',
        int $code = 400
    ) {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], $code);
    }
}
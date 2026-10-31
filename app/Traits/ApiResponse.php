<?php

namespace App\Traits;

trait ApiResponse
{
    /**
     * @param  array<string, string|int|float>  $replace
     */
    protected function success(
        mixed $data = null,
        string $message = 'messages.success',
        int $code = 200,
        array $replace = []
    ) {
        return response()->json([
            'success' => true,
            'message' => __($message, $replace),
            'data' => $data,
        ], $code);
    }

    /**
     * @param  array<string, string|int|float>  $replace
     */
    protected function error(
        string $message = 'messages.error',
        int $code = 400,
        array $replace = []
    ) {
        return response()->json([
            'success' => false,
            'message' => __($message, $replace),
        ], $code);
    }
}

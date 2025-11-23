<?php

namespace App\Helper;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    protected function success($data = [], $code = 200, $message = 'Success'): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    protected function error($error = [], $message = 'Error', $code = 400): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $error,
        ], $code);
    }
}

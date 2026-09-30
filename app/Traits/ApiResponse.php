<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    /**
     * Return a success JSON response.
     */
    protected function successResponse(mixed $data = null, ?string $message = null, int $status = 200, array $extra = []): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message ?? __('messages.success'),
            'data'    => $data,
        ];

        if (!empty($extra)) {
            $response = array_merge($response, $extra);
        }

        return response()->json($response, $status);
    }

    /**
     * Return an error JSON response.
     */
    protected function errorResponse(?string $message = null, int $status = 400, mixed $errors = null): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message ?? __('messages.error'),
        ];

        if (!is_null($errors)) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $status);
    }
}

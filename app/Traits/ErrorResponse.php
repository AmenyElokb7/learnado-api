<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ErrorResponse
{
    /**
     * Generates an error response.
     *
     * @param string $message
     * @param int $status
     * @return JsonResponse
     */
    public final function returnErrorResponse(string $message, int $status): JsonResponse
    {
        $response = [
            'message' => __('messages.' . $message),
        ];

        return response()->json($response, $status);
    }
}

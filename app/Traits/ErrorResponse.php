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
     * @param mixed $errors
     * @return JsonResponse
     */
    public final function returnErrorResponse(string $message, int $status, $errors = null): JsonResponse
    {
        $response = [
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $status);
    }
}

<?php


namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait SuccessResponse
{
    /**
     * Generates a success response.
     *
     * @param string $message
     * @param mixed|null $data
     * @param int $status
     * @return JsonResponse
     */
    public final function returnSuccessResponse(string $message, mixed $data, int $status): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    /**
     * @param mixed $data
     * @param int $status
     * @param bool $isPaginated
     * @return JsonResponse
     */

    public final function returnSuccessPaginationResponse($data, int $status, bool $isPaginated): JsonResponse
    {

        if ($isPaginated) {
            return response()->json([
                'message' => 'Success',
                'data' => $data->items(),
                'meta' => [
                    'current_page' => $data->currentPage(),
                    'per_page' => $data->perPage(),
                    'total' => $data->total(),
                ],
            ], $status);
        } else {
            return response()->json([
                'message' => 'Success',
                'data' => $data,
            ], $status);
        }
    }

}

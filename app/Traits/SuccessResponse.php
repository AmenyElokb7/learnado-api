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
            'message' => __('messages.' . $message),
            'data' => $data,
        ], $status);
    }

    /**
     * @param string $message
     * @param mixed $data
     * @param int $status
     * @param bool $isPaginated
     * @return JsonResponse
     */

    public final function returnSuccessPaginationResponse(string $message, mixed $data, int $status, bool $isPaginated): JsonResponse
    {

        if ($isPaginated) {
            return response()->json([
                'message' => __('messages.' . $message),
                'data' => $data->items(),
                'meta' => [
                    'current_page' => $data->currentPage(),
                    'per_page' => $data->perPage(),
                    'total' => $data->total(),
                ],
            ], $status);
        } else {
            return response()->json([
                'message' => __('messages.' . $message),
                'data' => $data,
            ], $status);
        }
    }

}

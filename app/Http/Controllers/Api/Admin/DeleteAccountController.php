<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\Admin\AdminRepository;
use App\Traits\ErrorResponse;
use App\Traits\SuccessResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * @OA\Delete(
 *     path="/api/admin/delete-user-account/{id}",
 *     summary="Delete a user account",
 *     tags={"Admin"},
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="The ID of the user account to delete",
 *         @OA\Schema(
 *             type="integer",
 *             example=123
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="User account deleted successfully",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="User account deleted successfully."
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Invalid request due to incorrect input or missing user ID"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="User not found"
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthorized - User not authorized to perform this action"
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Internal Server Error - Failed to delete user account"
 *     )
 * )
 */

class DeleteAccountController extends Controller
{
    protected $adminRepository;
    use SuccessResponse, ErrorResponse;

    public function __construct(AdminRepository $adminRepository)
    {
        $this->adminRepository = $adminRepository;
    }

    /**
     * @param int $userId
     * @return JsonResponse
     */

    public function __invoke(int $userId): JsonResponse
    {
        try {
            $this->adminRepository->deleteUserAccount($userId);
            return $this->returnSuccessResponse(__('user_deleted'), null, ResponseAlias::HTTP_OK);
        } catch (Exception $exception) {
            Log::error($exception->getMessage());
            return $this->returnErrorResponse(__('general_error'), ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}

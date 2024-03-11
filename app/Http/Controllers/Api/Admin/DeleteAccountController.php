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
 *     path="/api/admin/delete-user-account/{email}",
 *     summary="Delete a user account",
 *     tags={"Admin"},
 *     @OA\Parameter(
 *         name="email",
 *         in="path",
 *         required=true,
 *         description="The email of the user to delete",
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         content={
 *         @OA\MediaType(
 *         mediaType="application/json",
 *               ),
 *           }
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Invalid request"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="User not found"
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
     * @param Request $request
     * @param int $user_id
     * @return JsonResponse
     */

    public function __invoke(Request $request, int $user_id): JsonResponse
    {
        try {
            $this->adminRepository->deleteUserAccount($user_id);
            return $this->returnSuccessResponse(__('user_deleted'), null, ResponseAlias::HTTP_OK);
        } catch (Exception $exception) {
            Log::error($exception->getMessage());
            return $this->returnErrorResponse(__('general_error'), ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}

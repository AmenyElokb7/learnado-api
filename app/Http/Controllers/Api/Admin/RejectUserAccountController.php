<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\Admin\AdminRepository;
use App\Traits\ErrorResponse;
use App\Traits\SuccessResponse;
use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * @OA\Post(
 *     path="/api/admin/reject-user-account/{id}",
 *     summary="Reject a user account",
 *     tags={"Admin"},
 *     security={
 *         {"bearerAuth": {}}
 *     },
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *             example=1
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="User account rejected successfully",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="User account rejected successfully"),
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Invalid request due to incorrect input or missing fields"
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthorized - User not authorized to perform this action"
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Internal Server Error - Failed to reject user account"
 *     )
 * )
 */

class RejectUserAccountController extends Controller
{
    protected $adminRepository;

    use SuccessResponse, ErrorResponse;

    /**
     * Handle the incoming request.
     */

    function __construct(AdminRepository $adminRepository)
    {
        $this->adminRepository = $adminRepository;
    }
    /**
     * @param $id
     * @return JsonResponse
     */

    public function __invoke($id) : JsonResponse
    {
        try {
            $this->adminRepository->rejectUserAccount($id);
            return $this->returnSuccessResponse(__('user_account_rejected'), null, ResponseAlias::HTTP_OK);
        } catch (\Exception $exception) {
            return $this->returnErrorResponse($exception->getMessage(), $exception->getCode() ?:ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\Admin\AdminRepository;
use App\Traits\ErrorResponse;
use App\Traits\SuccessResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * @OA\Post(
 *     path="/api/admin/suspend-account",
 *     summary="Suspend a user account",
 *     tags={"Admin"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(
 *                 type="object",
 *                 required={"email"},
 *                 @OA\Property(
 *                     property="email",
 *                     type="string",
 *                     format="email",
 *                     example="testuser@example.com"
 *                 ),
 *             )
 *         )
 *     ),
 *     @OA\Response(response=200, description="User account suspended successfully",
 *              content={
 *          @OA\MediaType(
 *          mediaType="application/json",
 *                ),
 *            }
 *     )
 * ),
 */
class SuspendUserAccountController extends Controller
{
    protected $adminRepository;
    use SuccessResponse, ErrorResponse;

    /**
     * Handle the incoming request.
     */
    public function __construct(AdminRepository $adminRepository)
    {
        $this->adminRepository = $adminRepository;
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function __invoke($id): JsonResponse
    {
        try {
            $this->adminRepository->suspendUserAccount($id);
            return $this->returnSuccessResponse(__('user_suspended'), null, ResponseAlias::HTTP_OK);
        } catch (Exception $exception) {
            Log::error($exception->getMessage());
            return $this->returnErrorResponse(__('general_error'), ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);

        }
    }
}

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
 * @OA\Info(
 *     title="Learnado API",
 *     version="1.0.0",
 *     description="This is a simple API for an e-learning application",
 *    ),
 * @OA\Post(
 *     path="/api/admin/validate-user-account",
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
class ValidateAccountController extends Controller
{
    protected $adminRepository;
    use SuccessResponse, ErrorResponse;

    public function __construct(AdminRepository $adminRepository)
    {
        $this->adminRepository = $adminRepository;
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */

    public function __invoke(Request $request, $id): JsonResponse
    {

        try {
            $this->adminRepository->validateUserAccount($id);
            return $this->returnSuccessResponse(__('user_validated'), null, ResponseAlias::HTTP_OK);

        } catch (Exception $exception) {

            Log::error($exception->getMessage());
            return $this->returnErrorResponse($exception->getMessage(), ResponseAlias::HTTP_BAD_REQUEST);
        }

    }
}

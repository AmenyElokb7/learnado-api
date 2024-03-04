<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\Admin\AdminRepository;
use App\Traits\ErrorResponse;
use App\Traits\SuccessResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * @OA\Post(
 *     path="/admin/suspend-account",
 *     summary="Suspend a user account",
 *     tags={"Admin"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"email"},
 *             @OA\Property(property="email", type="string", format="email", example="testuser@example.com")
 *     ),
 *    ),
 *     @OA\Response(response=200, description="User account suspended successfully")
 *  ),
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
     * @param Request $request
     * @return JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $this->adminRepository->suspendUserAccount($request->email);
            return $this->returnSuccessResponse('User account suspended successfully', null, ResponseAlias::HTTP_OK);
        } catch (Exception $exception) {
            return $this->returnErrorResponse($exception->getMessage(), $exception->getCode());

        }

    }
}

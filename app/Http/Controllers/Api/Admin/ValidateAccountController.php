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
 * @OA\Info(
 *     title="Learnado API",
 *     version="1.0.0",
 *     description="This is a simple API for an e-learning application",
 *    ),
 * @OA\Post(
 *    path="/admin/validate-user-account",
 *   summary="Suspend a user account",
 *  tags={"Admin"},
 *     @OA\Response(response=200, description="User account validated successfully"),
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

    public function __invoke(Request $request): JsonResponse
    {
        $email = $request->input('email');

        try {
            $this->adminRepository->validateUserAccount($email);
            return $this->returnSuccessResponse('User account validated successfully', '', ResponseAlias::HTTP_OK);
        } catch (Exception $exception) {
            return $this->returnErrorResponse($exception->getMessage(), ResponseAlias::HTTP_BAD_REQUEST);
        }

    }
}

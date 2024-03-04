<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUserAccountRequest;
use App\Repositories\Admin\AdminRepository;
use App\Traits\ErrorResponse;
use App\Traits\SuccessResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 *
 * @OA\Patch(
 *     path="/update-user-account/{id}",
 *     summary="Update a user account",
 *     tags={"Admin"},
 * @OA\Response(
 *     response=401,
 *     description="You are not authorized to update this account",
 * ),
 * @OA\Response(
 *     response=200,
 *     description="User account updated successfully",
 *     ),
 * ),
 */
class UpdateUserAccountController extends Controller
{
    protected $adminRepository;
    use SuccessResponse, ErrorResponse;

    public function __construct(AdminRepository $adminRepository)
    {
        $this->adminRepository = $adminRepository;
    }

    public function __invoke(UpdateUserAccountRequest $request, int $id): JsonResponse
    {
        $data = $request->validated();
        try {
            $user = $this->adminRepository->updateUserAccount($id, $data);
            return $this->returnSuccessResponse('User account updated successfully', $user, ResponseAlias::HTTP_OK);
        } catch (Exception $exception) {
            Log::error($exception->getMessage());
            return $this->returnErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }

}

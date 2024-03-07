<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUserAccountRequest;
use App\Models\Admin;
use App\Repositories\Admin\AdminRepository;
use App\Traits\ErrorResponse;
use App\Traits\SuccessResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * @OA\Patch(
 *     path="/api/update-user-account/{id}",
 *     summary="Update a user account",
 *     tags={"Admin"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="The ID of the user account to update",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         description="Data for updating a user account",
 *         @OA\JsonContent(
 *             required={"email", "first_name", "last_name"},
 *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
 *             @OA\Property(property="first_name", type="string", example="John"),
 *             @OA\Property(property="last_name", type="string", example="Doe"),
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="User account updated successfully",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="User account updated successfully"),
 *             @OA\Property(
 *                 property="user",
 *                 type="object",
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="You are not authorized to update this account",
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Invalid request",
 *     )
 * )
 */
class UpdateAccountController extends Controller
{
    protected $adminRepository;
    use SuccessResponse, ErrorResponse;

    public function __construct(AdminRepository $adminRepository)
    {
        $this->adminRepository = $adminRepository;
    }

    /**
     * @param UpdateUserAccountRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function __invoke(UpdateUserAccountRequest $request, int $id): JsonResponse
    {
        $data = $this->getAttributes($request);
        try {
            $user = $this->adminRepository->updateUserAccount($id, $data);
            return $this->returnSuccessResponse(__('user_update'), $user, ResponseAlias::HTTP_OK);
        } catch (Exception $exception) {
            Log::error($exception->getMessage());
            return $this->returnErrorResponse($exception->getMessage() ?: __('general_error'), $exception->getCode() ?: ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function getAttributes(UpdateUserAccountRequest $request): array
    {
        return $request->validated();
    }
}

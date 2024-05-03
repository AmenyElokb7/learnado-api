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
 * @OA\Patch(
 *     path="/api/admin/update-user-account/{id}",
 *     summary="Update a user account",
 *     tags={"Admin"},
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="The ID of the user account to update",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\RequestBody(
 *         required=false,
 *         description="Optional data for updating a user account. At least one field must be provided, excluding email.",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="first_name", type="string", example="John"),
 *             @OA\Property(property="last_name", type="string", example="Doe"),
 *             @OA\Property(property="profile_picture", type="string", format="binary", description="Profile picture of the user"),
 *             @OA\Property(property="role", type="integer", description="User role", example=1)
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="User account updated successfully",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="User account updated successfully"
 *             ),
 *             @OA\Property(
 *                 property="user",
 *                 type="object",
 *                 description="The updated user object.",
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="first_name", type="string", example="John"),
 *                 @OA\Property(property="last_name", type="string", example="Doe"),
 *                 @OA\Property(property="profile_picture", type="string", example="url_to_picture"),
 *                 @OA\Property(property="role", type="integer", example=2)
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Invalid request due to incorrect input or validation failure",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="Validation error messages"
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthorized - User not authorized to perform this action",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="Unauthorized access"
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="User account not found",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="User not found"
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Internal Server Error - Failed to update user account",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="An internal server error has occurred"
 *             )
 *         )
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

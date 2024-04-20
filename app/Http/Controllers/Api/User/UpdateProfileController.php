<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProfileRequest;
use App\Repositories\User\UserRepository;
use App\Traits\ErrorResponse;
use App\Traits\SuccessResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * @OA\Patch(
 *     path="/api/update-profile",
 *     summary="Update user profile",
 *     tags={"User"},
 *     security={{"bearerAuth":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         description="Data for updating the user profile. All fields are optional but at least one should be provided.",
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(
 *                 type="object",
 *                 @OA\Property(
 *                     property="first_name",
 *                     type="string",
 *                     description="User's first name",
 *                     example="John"
 *                 ),
 *                 @OA\Property(
 *                     property="last_name",
 *                     type="string",
 *                     description="User's last name",
 *                     example="Doe"
 *                 ),
 *                 @OA\Property(
 *                     property="password",
 *                     type="string",
 *                     format="password",
 *                     description="New password for the user account. Must meet the defined complexity requirements."
 *                 ),
 *                 @OA\Property(
 *                     property="profile_picture",
 *                     type="string",
 *                     format="binary",
 *                     description="New profile picture for the user."
 *                 ),
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Profile updated successfully",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="Profile updated successfully"
 *             ),
 *
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Invalid request data",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="Invalid data provided"
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="User not found",
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
 *         description="Internal Server Error",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="An error occurred while processing your request."
 *             )
 *         )
 *     )
 * )
 */
class UpdateProfileController extends Controller
{

    use ErrorResponse, SuccessResponse;

    protected $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @param UpdateProfileRequest $request
     * @return JsonResponse
     */


    public function __invoke(UpdateProfileRequest $request): JsonResponse
    {

        $user = $this->getAttributes($request);
        try {
            $userProfileData = $this->userRepository->updateProfile($user);
            return $this->returnSuccessResponse(__('user_update'), $userProfileData, ResponseAlias::HTTP_OK);
        } catch (Exception $exception) {
            Log::error($exception->getMessage());
            return $this->returnErrorResponse($exception->getMessage() ?: __('general_error'), $exception->getCode() ?: ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param UpdateProfileRequest $request
     * @return array
     */

    private function getAttributes(UpdateProfileRequest $request): array
    {
        return $request->validated();

    }
}

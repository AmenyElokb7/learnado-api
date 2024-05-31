<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enum\UserRoleEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateUserAccountRequest;
use App\Repositories\Admin\AdminRepository;
use App\Traits\ErrorResponse;
use App\Traits\SuccessResponse;
use Exception;
use Illuminate\Support\Facades\Log;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;


/**
 * @OA\Post(
 *     path="/api/admin/create-user",
 *     summary="Create a new user account",
 *     tags={"Admin"},
 *     security={
 *         {"bearerAuth": {}}
 *     },
 *     @OA\RequestBody(
 *         required=true,
 *         content={
 *             @OA\MediaType(
 *                 mediaType="multipart/form-data",
 *                 @OA\Schema(
 *                     required={"first_name", "last_name", "email", "role"},
 *                     @OA\Property(property="first_name", type="string", example="John"),
 *                     @OA\Property(property="last_name", type="string", example="Doe"),
 *                     @OA\Property(property="email", type="string", format="email", example="JohnDoe@example.com"),
 *                     @OA\Property(property="role", type="integer", description="User role", example=1),
 *                     @OA\Property(property="profile_picture", type="string", format="binary", description="Profile picture of the user"),
 *                 )
 *             ),
 *         }
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="User account created successfully",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="User account created successfully"),
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
 *         description="Internal Server Error - Failed to create user account"
 *     )
 * )
 */


class CreateAccountController extends Controller
{
    protected $adminRepository;

    use SuccessResponse, ErrorResponse;

    public function __construct(AdminRepository $adminRepository)
    {
        $this->adminRepository = $adminRepository;
    }

    /**
     * @param CreateUserAccountRequest $request
     * @return ResponseAlias
     * @throws Exception
     */
    public function __invoke(CreateUserAccountRequest $request): ResponseAlias
    {
        $validatedData = $this->getAttributes($request);
        $accountType = $request->input('role');

            if ($accountType === UserRoleEnum::ADMIN->value) {
                return $this->returnerrorResponse(__('user_not_authorized'), ResponseAlias::HTTP_UNAUTHORIZED);
            }
            $user = $this->adminRepository->createUserAccount($validatedData);
            return $this->returnSuccessResponse(__('user_created'), $user, ResponseAlias::HTTP_OK);

    }

    /**
     * @param CreateUserAccountRequest $request
     * @return array
     */
    private function getAttributes(CreateUserAccountRequest $request): array
    {
        return $request->validated();
    }
}

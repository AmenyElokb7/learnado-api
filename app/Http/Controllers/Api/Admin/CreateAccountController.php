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
 *     @OA\RequestBody(
 *         required=true,
 *         content={
 *             @OA\MediaType(
 *                 mediaType="multipart/form-data",
 *                 @OA\Schema(
 *                     type="object",
 *                     @OA\Property(property="first_name",type="string",example="John"),
 *                     @OA\Property(property="last_name",type="string",example="Doe"),
 *                     @OA\Property(property="email",type="string",example="JohnDoe@example.com"),
 *                     @OA\Property(property="password",type="string",format="password",example="12345678"),
 *                     @OA\Property(property="password_confirmation",type="string",format="password",example="12345678"),
 *                     @OA\Property(property="profile_picture",type="string",format="binary",description="Profile picture of the user"),
 *                 )
 *             ),
 *         }
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *              content={
 *              @OA\MediaType(
 *                  mediaType="application/json",
 *              ),
 *          }
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Invalid request"
 *     )
 * )
 */
class CreateAccountController extends Controller
{
    protected $adminRepository;

    /**
     * Handle the incoming request.
     */
    use SuccessResponse, ErrorResponse;

    public function __construct(AdminRepository $adminRepository)
    {
        $this->adminRepository = $adminRepository;
    }

    /**
     * @param CreateUserAccountRequest $request
     * @return ResponseAlias
     */
    public function __invoke(CreateUserAccountRequest $request): ResponseAlias
    {
        $validatedData = $this->getAttributes($request);
        $accountType = $request->input('role');
        try {
            if ($accountType === UserRoleEnum::ADMIN->value) {
                return $this->returnerrorResponse(__('user_not_authorized'), ResponseAlias::HTTP_UNAUTHORIZED);
            }
            $user = $this->adminRepository->createUserAccount($validatedData);
            return $this->returnSuccessResponse(__('user_created'), $user, ResponseAlias::HTTP_OK);
        } catch (Exception $e) {
            Log::error('User not created', ['error' => $e->getMessage()]);
            return $this->returnErrorResponse(__('general_error'), ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function getAttributes(CreateUserAccountRequest $request): array
    {
        return $request->validated();
    }
}

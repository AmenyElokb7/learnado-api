<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\SetPasswordRequest;
use App\Repositories\User\UserRepository;
use App\Traits\ErrorResponse;
use App\Traits\SuccessResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * @OA\Post(
 *     path="/api/password-set",
 *     summary="Set a user password",
 *     tags={"Auth"},
 *     @OA\RequestBody(
 *         required=true,
 *         description="Payload to set a new password for a user",
 *         @OA\JsonContent(
 *             required={"token", "email", "password"},
 *             @OA\Property(property="token", type="string", example="token"),
 *             @OA\Property(property="email", type="string", format="email", example="testuser@example.com"),
 *             @OA\Property(property="password", type="string", format="password", example="12345678Aa", description="Password must be at least 8 characters long and contain a mix of letters, numbers, and symbols."),
 *             @OA\Property(property="password_confirmation", type="string", format="password", example="12345678Aa", description="password must much"),
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Password set successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Password set successfully."),
 *         ),
 *     ),
 *     @OA\Response(response=400, description="Bad Request - Invalid request format or parameters"),
 *     @OA\Response(response=401, description="Unauthorized - Invalid token"),
 *     @OA\Response(response=500, description="Internal Server Error - Failed to set password"),
 * ),
 */
class SetPasswordController extends Controller
{

    use SuccessResponse, ErrorResponse;

    protected $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function __invoke(SetPasswordRequest $request): JsonResponse
    {
        $token = $request->query('token');
        $newPassword = $request->input('password');
        try {
            $this->userRepository->setPassword($token, $newPassword);
            return $this->returnSuccessResponse(__('password_reset'), null, ResponseAlias::HTTP_OK);
        } catch (Exception $exception) {
            Log::error($exception->getMessage());
            return $this->returnErrorResponse($exception->getMessage() ?: __('general_error'), $exception->getCode() ?: ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegistrationRequest;
use App\Repositories\Auth\AuthRepository;
use App\Traits\ErrorResponse;
use App\Traits\SuccessResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * @OA\Post(
 *     path="/api/register",
 *     summary="Register a new user",
 *     tags={"Auth"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(
 *                 required={"first_name", "last_name", "email", "password", "password_confirmation", "role"},
 *                 @OA\Property(property="first_name", type="string", maxLength=255, example="Ameny"),
 *                 @OA\Property(property="last_name", type="string", maxLength=255, example="Elokb"),
 *                 @OA\Property(property="email", type="string", format="email", maxLength=255, example="ameny.elokb@example.com"),
 *                 @OA\Property(property="password", type="string", format="password", description="Minimum length based on configuration, must match password confirmation, and adhere to defined regex", example="Password123!"),
 *                 @OA\Property(property="password_confirmation", type="string", format="password", example="Password123!"),
 *                 @OA\Property(property="profile_picture", type="string", format="binary", description="Profile picture file, adheres to defined MIME types and max file size"),
 *                 @OA\Property(property="role", type="integer", description="User role, must be one of the predefined enum values", example=1),
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="User registered successfully"),
 *             @OA\Property(property="data", type="object")
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Invalid request",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Invalid request data")
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation Error",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Validation errors"),
 *             @OA\Property(property="errors", type="object")
 *         )
 *     )
 * )
 */
class RegisterController extends Controller
{

    use SuccessResponse, ErrorResponse;

    protected AuthRepository $authService;

    public function __construct(AuthRepository $authService)
    {
        $this->authService = $authService;
    }

    /**
     * @param RegistrationRequest $request
     * @return JsonResponse
     */
    public function __invoke(RegistrationRequest $request): JsonResponse
    {

        $validatedData = $this->getAttributes($request);
        try {
            $account = $this->authService->register($validatedData);
            return $this->returnSuccessResponse(__('user_registered'), $account, ResponseAlias::HTTP_OK);
        } catch (Exception $exception) {
            Log::error($exception->getMessage());
            return $this->returnErrorResponse(__('general_error'), $exception->getCode() ?: ResponseAlias::HTTP_BAD_REQUEST);
        }
    }

    private function getAttributes(RegistrationRequest $request): array
    {
        return $request->validated();
    }
}

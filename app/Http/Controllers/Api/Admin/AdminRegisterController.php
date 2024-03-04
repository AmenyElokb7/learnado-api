<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegistrationRequest;
use App\Repositories\Auth\AuthRepository;
use App\Traits\ErrorResponse;
use App\Traits\SuccessResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * @OA\Post(
 *     path="/register/admin",
 *     summary="Register a new admin user",
 *     tags={"Admin"},
 *     @OA\RequestBody(
 *     required=true,
 *     @OA\JsonContent(
 *     required={"first_name", "last_name", "email", "password", "account_type"},
 *     @OA\Property(property="first_name", type="string", example="John"),
 *     @OA\Property(property="last_name", type="string", example="Doe"),
 *     @OA\Property(property="email", type="string", example="JohnDoe@gmail.com"),
 *     @OA\Property(property="password", type="string", example="12345678"),
 *     @OA\Property(property="account_type", type="string", example="admin"),
 *     @OA\Property(property="profile_picture", type="file", example="profile_picture.jpg"),
 * )
 * ),
 *
 *     @OA\Response(response=200, description="Successful operation"),
 *     @OA\Response(response=400, description="Invalid request")
 * )
 */
class AdminRegisterController extends Controller
{

    use SuccessResponse, ErrorResponse;

    protected AuthRepository $authService;

    public function __construct(AuthRepository $authService)
    {
        $this->authService = $authService;
    }

    public function __invoke(RegistrationRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        try {
            $accountType = $validatedData['account_type'];
            $account = $this->authService->register($validatedData, $accountType);
            $responseMessage = ucfirst($accountType) . ' registered successfully';
            return $this->returnSuccessResponse($responseMessage, ['account' => $account], ResponseAlias::HTTP_OK);
        } catch (Exception $exception) {
            return $this->returnErrorResponse($exception->getMessage(), $exception->getCode() ?: ResponseAlias::HTTP_BAD_REQUEST);
        }
    }
}

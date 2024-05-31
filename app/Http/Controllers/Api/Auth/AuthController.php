<?php

namespace App\Http\Controllers\Api\Auth;


use App\Http\Controllers\Controller;
use App\Http\Requests\AuthenticateUserRequest;
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
 *     path="/api/login",
 *     summary="Authenticate a user",
 *     tags={"Auth"},
 *     @OA\RequestBody(
 *     required=true,
 *     @OA\JsonContent(
 *     required={"email", "password"},
 *     @OA\Property(property="email", type="string", example="testuser@example.com"),
 *     @OA\Property(property="password", type="string", example="12345678"),
 *     )
 * ),
 *     @OA\Response(response=200, description="Login Successful"),
 *     @OA\Response(response=400, description="Invalid request"),
 *     @OA\Response(response=401, description="Unauthorized"),
 *     @OA\Response(response=500, description="Internal server error")
 * )
 */
class AuthController extends Controller
{

    use ErrorResponse, SuccessResponse;

    protected $authRepository;

    public function __construct(AuthRepository $authRepository)
    {
        $this->authRepository = $authRepository;
    }

    /**
     * @param AuthenticateUserRequest $request
     * @return JsonResponse
     */
    public function __invoke(AuthenticateUserRequest $request): JsonResponse
    {
        $credentials = $this->getAttributes($request);
        try {
            $result = $this->authRepository->authenticate($credentials);
            return $this->returnSuccessResponse(__('user_authenticated'), $result, ResponseAlias::HTTP_OK);
        } catch (Exception $exception) {
            Log::error($exception->getMessage());
            $errorDetails = json_decode($exception->getMessage(), true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($errorDetails)) {
                return response()->json(['errors' => $errorDetails], $exception->getCode() ?: ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
            } else {
                return $this->returnErrorResponse( $exception->getMessage() ?: __('general_error'), $exception->getCode() ?:ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
            }

        }
    }

    /**
     * @param AuthenticateUserRequest $request
     * @return array
     */
    private function getAttributes(AuthenticateUserRequest $request): array
    {
        return $request->validated();
    }
}

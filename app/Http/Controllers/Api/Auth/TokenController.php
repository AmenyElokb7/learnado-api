<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Repositories\Auth\AuthRepository;
use App\Traits\ErrorResponse;
use App\Traits\SuccessResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * @OA\Post(
 *     path="/api/refresh-token",
 *     summary="Get a new access token",
 *     tags={"Auth"},
 *     @OA\RequestBody(
 *         required=true,
 *         description="Refresh token required to obtain a new access token",
 *         @OA\JsonContent(
 *             required={"refresh_token"},
 *             @OA\Property(property="refresh_token", type="string", example="YourRefreshTokenHere"),
 *         ),
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(
 *             @OA\Property(property="access_token", type="string", example="NewAccessTokenHere"),
 *             @OA\Property(property="expires_in", type="integer", example=3600),
 *         ),
 *     ),
 *     @OA\Response(response=401, description="Unauthorized, invalid or expired refresh token"),
 *     @OA\Response(response=500, description="Internal server error, e.g., failed to generate an access token"),
 *     security={}
 * ),
 */
class TokenController extends Controller
{
    /**
     * Handle the incoming request.
     */
    protected $authService;
    use ErrorResponse, SuccessResponse;

    public function __construct(AuthRepository $authService)
    {
        $this->authService = $authService;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $refresh = $this->authService->refreshToken();
            return $this->returnSuccessResponse(__('token_refreshed'), $refresh, ResponseAlias::HTTP_OK);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return $this->returnErrorResponse($e->getMessage(), ResponseAlias::HTTP_UNAUTHORIZED);
        }
    }
}

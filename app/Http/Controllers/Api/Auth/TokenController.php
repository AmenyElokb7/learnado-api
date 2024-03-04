<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Repositories\Auth\AuthRepository;
use App\Traits\ErrorResponse;
use App\Traits\SuccessResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

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


    public function __invoke(Request $request): JsonResponse
    {
        $refreshToken = $request->bearerToken();
        try {
            $payload = JWTAuth::parseToken()->getPayload();
            if ($payload['token_type'] !== 'refresh') {
                return response()->json(['error' => 'Invalid token type'], 401);
            }
            $user = JWTAuth::authenticate($refreshToken);
            $newAccessToken = JWTAuth::claims(['token_type' => 'access'])->fromUser($user);
            return response()->json([
                'status' => 'success',
                'access_token' => $newAccessToken,
                'refresh_token' => $refreshToken,
                'user' => $user
            ]);
        } catch (TokenInvalidException) {
            return $this->returnErrorResponse('Token is invalid', ResponseAlias::HTTP_UNAUTHORIZED);
        } catch (JWTException $exception) {
            return $this->returnErrorResponse($exception->getMessage(), ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

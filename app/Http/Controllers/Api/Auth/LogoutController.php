<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Traits\ErrorResponse;
use App\Traits\SuccessResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Tymon\JWTAuth\Facades\JWTAuth;

class LogoutController extends Controller
{
    use ErrorResponse, SuccessResponse;

    public function __invoke(Request $request): JsonResponse
    {
        try {
            $refreshToken = $request->input('refresh_token');
            if ($refreshToken) {
                Log::info('Refresh token: ' . $refreshToken);
                JWTAuth::setToken($refreshToken)->invalidate();
            }
            Auth::logout();
            return $this->returnSuccessResponse('User logged out successfully', null, ResponseAlias::HTTP_OK);
        } catch (Exception $e) {
            return $this->returnErrorResponse($e->getMessage(), ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}

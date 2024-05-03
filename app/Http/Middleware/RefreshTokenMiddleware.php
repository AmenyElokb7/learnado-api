<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Validation\UnauthorizedException;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class RefreshTokenMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $check = auth()->check();
        try {
            $isRefreshToken = JWTAuth::setToken(JWTAuth::getToken())->getPayload()->get('is_refresh-token');
            if (!$check || !$isRefreshToken) {
                throw new UnauthorizedException(__('messages.user_not_authorized'));
            }
            return $next($request);
        } catch (TokenExpiredException $exception) {
            throw new UnauthorizedException(__('token_expired'));
        } catch (TokenInvalidException $e) {
            throw new UnauthorizedException(__('token_invalid'));
        }
    }
}

<?php

namespace App\Http\Middleware;

use App\Enum\UserRoleEnum;
use App\Traits\ErrorResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class Admin
{
    use ErrorResponse;

    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (ResponseAlias) $next
     */
    public final function handle(Request $request, Closure $next): mixed
    {
        if (auth()->user()->role === UserRoleEnum::ADMIN->value) {
            return $next($request);
        }
        return $this->returnErrorResponse(__('user_not_authorized'), ResponseAlias::HTTP_FORBIDDEN);
    }
}

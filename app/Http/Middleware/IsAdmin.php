<?php

namespace App\Http\Middleware;

use App\Traits\ErrorResponse;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class IsAdmin
{
    use ErrorResponse;

    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public final function handle(Request $request, Closure $next): mixed
    {
        if (Auth::guard('admin')->check() && Auth::guard('admin')->user()->role === 'admin') {
            return $next($request);
        }
        return $this->returnErrorResponse('You are not authorized to access this route', ResponseAlias::HTTP_FORBIDDEN);
    }
}

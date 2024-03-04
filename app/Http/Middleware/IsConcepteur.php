<?php

namespace App\Http\Middleware;

use App\Traits\ErrorResponse;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IsConcepteur
{
    use ErrorResponse;

    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::guard('admin')->check() && Auth::guard('admin')->user()->role === 'concepteur') {
            return $next($request);
        }
        return $this->returnErrorResponse('You are not authorized to access this route', Response::HTTP_FORBIDDEN);

    }
}

<?php

namespace App\Http\Middleware;

use App\Enum\UserRoleEnum;
use App\Traits\ErrorResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Facilitator
{

    use ErrorResponse;

    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->user()->role === UserRoleEnum::FACILITATOR->value) {
            return $next($request);
        }
        return $this->returnErrorResponse(__('user_not_authorized'), Response::HTTP_FORBIDDEN);
    }
}

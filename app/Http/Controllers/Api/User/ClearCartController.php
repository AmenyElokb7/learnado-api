<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Repositories\Course\CourseRepository;
use App\Traits\ErrorResponse;
use App\Traits\SuccessResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class ClearCartController extends Controller
{
    /**
     * Handle the incoming request.
     * @param Request $request
     * @return JsonResponse
     */

    use ErrorResponse, SuccessResponse;
    public function __invoke(Request $request): JsonResponse
    {
        try{
            CourseRepository::clearCart();
            return $this->returnSuccessResponse(__('cart_cleared'), null, ResponseAlias::HTTP_OK);
        }catch (\Exception $e){
            Log::error($e->getMessage());
            return $this->returnErrorResponse($e->getMessage(), ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Repositories\Course\CourseRepository;
use App\Traits\ErrorResponse;
use App\Traits\SuccessResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class RemoveFromCartController extends Controller
{
    use SuccessResponse, ErrorResponse;

    public function __invoke($course_id) : JsonResponse
    {
        try{
            CourseRepository::removeFromCart($course_id);
            return $this->returnSuccessResponse('Course removed from cart successfully', null, ResponseAlias::HTTP_OK);
        }catch(\Exception $e){
            Log::error($e->getMessage());
            return $this->returnErrorResponse($e->getMessage(), ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

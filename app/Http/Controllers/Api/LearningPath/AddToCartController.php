<?php

namespace App\Http\Controllers\Api\LearningPath;

use App\Http\Controllers\Controller;
use App\Repositories\LearningPath\LearningPathRepository;
use App\Traits\ErrorResponse;
use App\Traits\SuccessResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class AddToCartController extends Controller
{
    /**
     * Handle the incoming request.
     */
    use SuccessResponse, ErrorResponse;
    public function __invoke($learning_path_id): JsonResponse
    {
            try{
                LearningPathRepository::addToCart($learning_path_id);
                return $this->returnSuccessResponse(__('learning_path_added_to_cart'), [], ResponseAlias::HTTP_OK);
            }catch(\Exception $e){
                Log::error($e->getMessage());
                return $this->returnErrorResponse($e->getMessage(), ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
            }

    }
}

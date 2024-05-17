<?php

namespace App\Http\Controllers\Api\LearningPath;

use App\Helpers\QueryConfig;
use App\Http\Controllers\Controller;
use App\Repositories\LearningPath\LearningPathRepository;
use App\Traits\ErrorResponse;
use App\Traits\SuccessResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class GetLearningPathByIdController extends Controller
{
    /**
     * Handle the incoming request.
     * @return JsonResponse
     * @param $learningPathId
     */
    use SuccessResponse,ErrorResponse;
    public function __invoke($learningPathId) : JsonResponse
    {

            $learningPath= LearningPathRepository::getLearningPathById($learningPathId);
            return $this->returnSuccessResponse('Learning Path fetched successfully',$learningPath, ResponseAlias::HTTP_OK);

    }
}

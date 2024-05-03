<?php

namespace App\Http\Controllers\Api\Quiz;

use App\Http\Controllers\Controller;
use App\Repositories\LearningPath\LearningPathRepository;
use App\Traits\ErrorResponse;
use App\Traits\SuccessResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class DeleteLearningPathQuizController extends Controller
{
    use ErrorResponse, SuccessResponse;

    protected $learningPathRepository;

    public function __construct(LearningPathRepository $learningPathRepository)
    {
        $this->learningPathRepository = $learningPathRepository;
    }

    public function __invoke($learningPathId): JsonResponse
    {
        try {
            $this->learningPathRepository->deleteQuizFromLearningPath($learningPathId);
            return $this->returnSuccessResponse(__('quiz_deleted'), null, ResponseAlias::HTTP_OK);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return $this->returnErrorResponse($e->getMessage(), ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }

    }
}

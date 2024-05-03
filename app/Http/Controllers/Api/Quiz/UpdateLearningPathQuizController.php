<?php

namespace App\Http\Controllers\Api\Quiz;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateQuizRequest;
use App\Repositories\LearningPath\LearningPathRepository;
use App\Traits\ErrorResponse;
use App\Traits\SuccessResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class UpdateLearningPathQuizController extends Controller
{
    use SuccessResponse, ErrorResponse;

    protected $learningPathRepository;

    public function __construct(LearningPathRepository $learningPathRepository)
    {
        $this->learningPathRepository = $learningPathRepository;
    }

    /**
     * @param UpdateQuizRequest $request
     * @return JsonResponse
     */
    public function __invoke(UpdateQuizRequest $request, $learningPathId): JsonResponse
    {
        $data = $request->validated();
        try {
            $learningPath = $this->learningPathRepository->updateLearningPathQuiz($data, $learningPathId);
            return $this->returnSuccessResponse(__('quiz_updated'), $learningPath, ResponseAlias::HTTP_OK);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return $this->returnErrorResponse($e->getMessage(), ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

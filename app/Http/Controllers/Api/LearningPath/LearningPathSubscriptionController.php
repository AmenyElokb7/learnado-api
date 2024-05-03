<?php

namespace App\Http\Controllers\Api\LearningPath;

use App\Http\Controllers\Controller;
use App\Repositories\LearningPath\LearningPathRepository;
use App\Traits\ErrorResponse;
use App\Traits\SuccessResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class LearningPathSubscriptionController extends Controller
{
    use SuccessResponse, ErrorResponse;

    protected $learningPathRepository;

    public function __construct(LearningPathRepository $learningPathRepository)
    {
        $this->learningPathRepository = $learningPathRepository;
    }

    public function __invoke($learningPathId): JsonResponse
    {
        try {
            $learningPath = $this->learningPathRepository->subscribeUsersToLearningPath($learningPathId);

            return $this->returnSuccessResponse(__('user_subscribed'), $learningPath, ResponseAlias::HTTP_OK);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return $this->returnErrorResponse($e->getMessage(), ResponseAlias::HTTP_FORBIDDEN);
        }
    }
}

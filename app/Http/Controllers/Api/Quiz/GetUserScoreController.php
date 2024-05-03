<?php

namespace App\Http\Controllers\Api\Quiz;

use App\Http\Controllers\Controller;
use App\Repositories\Quiz\QuizRepository;
use App\Traits\ErrorResponse;
use App\Traits\SuccessResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class GetUserScoreController extends Controller
{
    use SuccessResponse, ErrorResponse;
    protected $quizRepository;

    public function __construct(QuizRepository $quizRepository)
    {
        $this->quizRepository = $quizRepository;
    }

    public function __invoke($quiz_id, $user_id): JsonResponse
    {
        try {
            $userScore = $this->quizRepository->getQuizScoreForUser($quiz_id, $user_id);
            return $this->returnSuccessResponse(__('user_score'), $userScore, ResponseAlias::HTTP_OK);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return $this->returnErrorResponse($e->getMessage(), ResponseAlias::HTTP_BAD_REQUEST);
        }
    }
}

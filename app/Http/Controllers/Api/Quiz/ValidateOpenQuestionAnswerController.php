<?php

namespace App\Http\Controllers\Api\Quiz;

use App\Http\Controllers\Controller;
use App\Models\LearningPath;
use App\Repositories\UserAnswers\UserAnswersRepository;
use App\Traits\ErrorResponse;
use App\Traits\SuccessResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class ValidateOpenQuestionAnswerController extends Controller
{
    /**
     * Handle the incoming request.
     */
    use SuccessResponse,ErrorResponse;
    public function __invoke($answer_id) : JsonResponse
    {
        try{
            $answer = UserAnswersRepository::validateOpenQuestion($answer_id);
            return $this->returnSuccessResponse(__('answer_validated'), $answer, ResponseAlias::HTTP_OK);
        }catch (\Exception $exception){
            Log::error($exception->getMessage());
            return $this->returnErrorResponse($exception->getMessage() ?: __('general_error'), ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

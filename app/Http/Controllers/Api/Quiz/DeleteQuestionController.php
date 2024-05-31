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

class DeleteQuestionController extends Controller
{
    /**
     * Handle the incoming request.
     */

    use SuccessResponse, ErrorResponse;

    public function __invoke($question_id) : JsonResponse
    {
        try {
            QuizRepository::deleteQuestion($question_id);
            return $this->returnSuccessResponse(__('messages.question_deleted'), null, ResponseAlias::HTTP_OK);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return $this->returnErrorResponse($e->getMessage() ?: __('messages.general_error'), $e->getCode() ?: ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}

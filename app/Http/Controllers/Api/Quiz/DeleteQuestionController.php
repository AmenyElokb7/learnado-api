<?php

namespace App\Http\Controllers\Api\Quiz;

use App\Http\Controllers\Controller;
use App\Repositories\Quiz\QuizRepository;
use App\Traits\ErrorResponse;
use App\Traits\SuccessResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class DeleteQuestionController extends Controller
{
    /**
     * Handle the incoming request.
     */

    use SuccessResponse, ErrorResponse;

    protected $questionRepository;

    public function __construct(QuizRepository $questionRepository)
    {
        $this->questionRepository = $questionRepository;
    }
    public function __invoke($question_id) : JsonResponse
    {
        try {
            $this->questionRepository->deleteQuestion($question_id);
            return $this->returnSuccessResponse(__('messages.question_deleted'), null, ResponseAlias::HTTP_OK);
        } catch (\Exception $e) {
            return $this->returnErrorResponse($e->getMessage() ?: __('messages.general_error'), $e->getCode() ?: ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}

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

class DeleteAnswerController extends Controller
{
    /**
     * Handle the incoming request.
     */

    use SuccessResponse, ErrorResponse;

    protected $answerRepository;

    public function __construct(QuizRepository $answerRepository)
    {
        $this->answerRepository = $answerRepository;
    }

    public function __invoke($answer_id) : JsonResponse
    {
        try{
            $this->answerRepository->deleteAnswer($answer_id);
            return $this->returnSuccessResponse(__('messages.answer_deleted'), null, ResponseAlias::HTTP_OK);

        }catch (\Exception $e) {
             Log::error($e->getMessage());
            return $this->returnErrorResponse($e->getMessage() ?: __('messages.general_error'), $e->getCode() ?: ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

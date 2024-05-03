<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserAnswersRequest;
use App\Repositories\UserAnswers\UserAnswersRepository;
use App\Traits\ErrorResponse;
use App\Traits\SuccessResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class UserAnswersController extends Controller
{
    use SuccessResponse, ErrorResponse;

    protected $userAnswers;

    public function __construct(UserAnswersRepository $userAnswers)
    {
        $this->userAnswers = $userAnswers;
    }

    public function __invoke(UserAnswersRequest $request, $quiz_id): JsonResponse
    {
        $user = auth()->user();
        try {
            $result = $this->userAnswers->submitQuizAnswers(
                $user->id,
                $quiz_id,
                $request->answers
            );

            return $this->returnSuccessResponse(__('user_quiz_answer'), $result, ResponseAlias::HTTP_OK);

        } catch (Exception $e) {
            Log::error($e->getMessage());
            return $this->returnErrorResponse($e->getMessage(), ResponseAlias::HTTP_BAD_REQUEST);
        }
    }
}

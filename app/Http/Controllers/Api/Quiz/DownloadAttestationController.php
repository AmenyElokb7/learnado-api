<?php

namespace App\Http\Controllers\Api\Quiz;

use App\Http\Controllers\Controller;
use App\Repositories\UserAnswers\UserAnswersRepository;
use App\Traits\ErrorResponse;
use App\Traits\SuccessResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class DownloadAttestationController extends Controller
{
    /**
     * Handle the incoming request.
     */
    protected $userAnswersRepository;
    public function __construct(UserAnswersRepository $userAnswersRepository)
    {
        $this->userAnswersRepository = $userAnswersRepository;
    }
    use SuccessResponse,ErrorResponse;
    public function __invoke($learning_path_id) : JsonResponse | Response
    {
        try {
            return $this->userAnswersRepository->downloadAttestation($learning_path_id);
        }catch (\Exception $exception) {
            Log::error($exception->getMessage());
            return $this->returnErrorResponse($exception->getMessage() ?: __('general_error'), ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

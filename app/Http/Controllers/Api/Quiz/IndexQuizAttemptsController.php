<?php

namespace App\Http\Controllers\Api\Quiz;

use App\Helpers\QueryConfig;
use App\Http\Controllers\Controller;
use App\Repositories\Quiz\QuizRepository;
use App\Traits\ErrorResponse;
use App\Traits\PaginationParams;
use App\Traits\SuccessResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class IndexQuizAttemptsController extends Controller
{
    /**
     * Handle the incoming request.
     */
    use SuccessResponse, ErrorResponse,PaginationParams;

    public function __invoke(Request $request): \Illuminate\Http\JsonResponse
    {
        $paginationParams = $this->getAttributes($request);
        try {
            $quizAttempts = QuizRepository::indexQuizAttempts($paginationParams);
            return $this->returnSuccessPaginationResponse(__('quiz_attempts_found'), $quizAttempts, ResponseAlias::HTTP_OK, $paginationParams->isPaginated());
        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
            return $this->returnErrorResponse($exception->getMessage() ?: __('general_error'), ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    private function getAttributes(Request $request): QueryConfig
    {
        $paginationParams = $this->getPaginationParams($request);
        $filters = [
            'keyword' => $paginationParams['KEYWORD'] ?? '',
        ];
        $search = new QueryConfig();
        $search->setFilters($filters)
            ->setPerPage($paginationParams['PER_PAGE'])
            ->setOrderBy($paginationParams['ORDER_BY'])
            ->setDirection($paginationParams['DIRECTION'])
            ->setPaginated($paginationParams['PAGINATION'])
            ->setPage($paginationParams['PAGE']);
        return $search;

    }
}

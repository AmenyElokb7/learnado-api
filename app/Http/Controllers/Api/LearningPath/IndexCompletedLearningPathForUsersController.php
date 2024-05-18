<?php

namespace App\Http\Controllers\Api\LearningPath;

use App\Helpers\QueryConfig;
use App\Http\Controllers\Controller;
use App\Models\Attestation;
use App\Repositories\LearningPath\LearningPathRepository;
use App\Traits\ErrorResponse;
use App\Traits\PaginationParams;
use App\Traits\SuccessResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class IndexCompletedLearningPathForUsersController extends Controller
{
    /**
     * Index completed learning paths for users.
     * Handle the incoming request.
     * @param Request $request
     * @return JsonResponse
     */
    use SuccessResponse,ErrorResponse, PaginationParams;
    public function __invoke(Request $request) : JsonResponse
    {
        $paginationParams = $this->getAttributes($request);
        try {
            $completedLearningPaths = LearningPathRepository::indexCompletedLearningsPathsForUser($paginationParams);
            return $this->returnSuccessPaginationResponse(__('completed_learning_paths_found'), $completedLearningPaths, ResponseAlias::HTTP_OK, $paginationParams->isPaginated());
        } catch (\Exception $exception) {
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

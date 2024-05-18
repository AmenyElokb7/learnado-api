<?php

namespace App\Http\Controllers\Api\LearningPath;

use App\Helpers\QueryConfig;
use App\Http\Controllers\Controller;
use App\Repositories\LearningPath\LearningPathRepository;
use App\Traits\ErrorResponse;
use App\Traits\PaginationParams;
use App\Traits\SuccessResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class IndexAttestationsController extends Controller
{
    /**
     * Index Attestations For users
     * Handle the incoming request.
     * @param Request $request
     * @return JsonResponse
     */
    use SuccessResponse,ErrorResponse,PaginationParams;

    public function __invoke(Request $request) :JsonResponse
    {
        $paginationParams = $this->getAttributes($request);
        try {
            $attestations = LearningPathRepository::indexLearningPathAttestationsForUsers($paginationParams);
            return $this->returnSuccessPaginationResponse(__('attestations_found'), $attestations, ResponseAlias::HTTP_OK, $paginationParams->isPaginated());
        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
            return $this->returnErrorResponse($exception->getMessage() ?: __('general_error'), ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    private function getAttributes(Request $request): QueryConfig
    {

        $paginationParams = $this->getPaginationParams($request);

        $search = new QueryConfig();
        $search->setPerPage($paginationParams['PER_PAGE'])
            ->setOrderBy($paginationParams['ORDER_BY'])
            ->setDirection($paginationParams['DIRECTION'])
            ->setPaginated($paginationParams['PAGINATION'])
            ->setPage($paginationParams['PAGE']);
        return $search;

    }
}

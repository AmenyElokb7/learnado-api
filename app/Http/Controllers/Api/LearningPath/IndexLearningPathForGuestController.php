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
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class IndexLearningPathForGuestController extends Controller
{
    /**
     * Handle the incoming request.
     */
    use SuccessResponse, ErrorResponse, PaginationParams;
    public function __invoke(Request $request): JsonResponse
    {
        $paginationParams = $this->getAttributes($request);
        try {
            $learningPaths = LearningPathRepository::index($paginationParams);
            return $this->returnSuccessPaginationResponse(__('learning_paths'), $learningPaths, ResponseAlias::HTTP_OK, $paginationParams->isPaginated());
        } catch (\Exception $exception) {
            return $this->returnErrorResponse($exception->getMessage() ?: __('general_error'), ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    private function getAttributes(Request $request): QueryConfig
    {
        $paginationParams = $this->getPaginationParams($request);

        $filters = [
            'public' => true,
            'active' => true,
            'offline' => false,
            'keyword' => $paginationParams['KEYWORD'] ?? '',
            'category' => $request->input('category', null),
            'price' => $request->input('price', null),
        ];
        $order_by = [
            'created_at',
            'title',
            'filter_price',

        ];
        $orderByField = in_array($paginationParams['ORDER_BY'], $order_by) ? $paginationParams['ORDER_BY'] : 'created_at';

        $search = new QueryConfig();
        $search->setFilters($filters)
            ->setPage($paginationParams['PAGE'])
            ->setPerPage($paginationParams['PER_PAGE'])
            ->setOrderBy($orderByField)
            ->setDirection($paginationParams['DIRECTION'])
            ->setPaginated($paginationParams['PAGINATION']);
        return $search;
    }
}

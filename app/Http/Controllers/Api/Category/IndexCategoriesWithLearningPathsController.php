<?php

namespace App\Http\Controllers\Api\Category;

use App\Helpers\QueryConfig;
use App\Http\Controllers\Controller;
use App\Repositories\Category\CategoryRepository;
use App\Traits\ErrorResponse;
use App\Traits\PaginationParams;
use App\Traits\SuccessResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class IndexCategoriesWithLearningPathsController extends Controller
{
    use SuccessResponse, ErrorResponse, PaginationParams;
    public function __invoke(Request $request) : JsonResponse
    {
        $paginationParams = $this->getAttributes($request);
        try {
            $categories = CategoryRepository::indexCategoriesWithLearningPaths($paginationParams);
            return $this->returnSuccessPaginationResponse(__('categories_found'),
                $categories,
                ResponseAlias::HTTP_OK, $paginationParams->isPaginated());
        } catch (\Exception $e) {
            return $this->returnErrorResponse($e->getMessage(), ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    private function getAttributes(Request $request): QueryConfig
    {
        $paginationParams = $this->getPaginationParams($request);
        $search = new QueryConfig();

        $filters = [
            'category ' => $request->input('keyword'),
        ];
        $search->setFilters($filters)
            ->setPerPage($paginationParams['PER_PAGE'])
            ->setOrderBy($paginationParams['ORDER_BY'])
            ->setDirection($paginationParams['DIRECTION'])
            ->setPaginated($paginationParams['PAGINATION']);
        return $search;

    }
}

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
use Illuminate\Support\Facades\Log;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * @OA\Get(
 *     path="/api/categories-learning-paths",
 *     summary="Retrieve categories with learning paths",
 *     tags={"Category"},
 *     @OA\Parameter(
 *         name="keyword",
 *         in="query",
 *         required=false,
 *         @OA\Schema(
 *             type="string",
 *             example="programming"
 *         )
 *     ),
 *     @OA\Parameter(
 *         name="page",
 *         in="query",
 *         required=false,
 *         @OA\Schema(
 *             type="integer",
 *             example=1
 *         )
 *     ),
 *     @OA\Parameter(
 *         name="per_page",
 *         in="query",
 *         required=false,
 *         @OA\Schema(
 *             type="integer",
 *             example=10
 *         )
 *     ),
 *     @OA\Parameter(
 *         name="order_by",
 *         in="query",
 *         required=false,
 *         @OA\Schema(
 *             type="string",
 *             example="created_at"
 *         )
 *     ),
 *     @OA\Parameter(
 *         name="direction",
 *         in="query",
 *         required=false,
 *         @OA\Schema(
 *             type="string",
 *             example="desc"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Categories retrieved successfully",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Categories found"),
 *             @OA\Property(property="data", type="array",
 *                 @OA\Items(
 *                     @OA\Property(property="id", type="integer", example=1),
 *                     @OA\Property(property="name", type="string", example="Technology"),
 *                     @OA\Property(property="description", type="string", example="Courses related to technology"),
 *                     @OA\Property(property="learning_paths", type="array",
 *                         @OA\Items(
 *                             @OA\Property(property="id", type="integer", example=1),
 *                             @OA\Property(property="name", type="string", example="Full Stack Development"),
 *                             @OA\Property(property="description", type="string", example="A complete path for full stack development")
 *                         )
 *                     ),
 *                     @OA\Property(property="created_at", type="string", format="date-time", example="2024-05-21T12:00:00Z"),
 *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-05-21T12:00:00Z")
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Internal Server Error - Failed to retrieve categories"
 *     )
 * )
 */

class IndexCategoriesWithLearningPathsController extends Controller
{
    use SuccessResponse, ErrorResponse, PaginationParams;

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function __invoke(Request $request) : JsonResponse
    {
        $paginationParams = $this->getAttributes($request);
        try {
            $categories = CategoryRepository::indexCategoriesWithLearningPaths($paginationParams);
            return $this->returnSuccessPaginationResponse(__('categories_found'),
                $categories,
                ResponseAlias::HTTP_OK, $paginationParams->isPaginated());
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return $this->returnErrorResponse($e->getMessage(), ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param Request $request
     * @return QueryConfig
     */
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

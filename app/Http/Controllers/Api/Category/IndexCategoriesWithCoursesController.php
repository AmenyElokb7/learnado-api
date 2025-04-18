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
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
/**
 * @OA\Get(
 *     path="/api/categories",
 *     summary="List categories with their courses including optional filtering and pagination",
 *     tags={"Category"},
 *     @OA\Parameter(
 *         name="keyword",
 *         in="query",
 *         description="Keyword to filter categories",
 *         required=false,
 *         @OA\Schema(
 *             type="string"
 *         )
 *     ),
 *     @OA\Parameter(
 *         name="PER_PAGE",
 *         in="query",
 *         description="Number of categories per page",
 *         required=false,
 *         @OA\Schema(
 *             type="integer",
 *             default=10
 *         )
 *     ),
 *     @OA\Parameter(
 *         name="ORDER_BY",
 *         in="query",
 *         description="Field to sort the categories",
 *         required=false,
 *         @OA\Schema(
 *             type="string",
 *             default="name"
 *         )
 *     ),
 *     @OA\Parameter(
 *         name="DIRECTION",
 *         in="query",
 *         description="Sorting direction, either ASC or DESC",
 *         required=false,
 *         @OA\Schema(
 *             type="string",
 *             enum={"ASC", "DESC"},
 *             default="ASC"
 *         )
 *     ),
 *     @OA\Parameter(
 *         name="PAGINATION",
 *         in="query",
 *         description="Enable or disable pagination",
 *         required=false,
 *         @OA\Schema(
 *             type="boolean",
 *             default=true
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="categories_found"
 *             ),
 *             @OA\Property(
 *                 property="success",
 *                 type="boolean",
 *                 example=true
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Internal Server Error",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="message",
 *                 type="string"
 *             ),
 *             @OA\Property(
 *                 property="success",
 *                 type="boolean",
 *                 example=false
 *             )
 *         )
 *     )
 * )
 */

class IndexCategoriesWithCoursesController extends Controller
{
    use SuccessResponse, ErrorResponse, PaginationParams;
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
    {
        $paginationParams = $this->getAttributes($request);
        try {
            $categories = CategoryRepository::indexCategoriesWithCourses($paginationParams);
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

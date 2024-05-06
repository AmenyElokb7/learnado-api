<?php

namespace App\Http\Controllers\Api\User;

use App\Helpers\QueryConfig;
use App\Http\Controllers\Controller;
use App\Repositories\Course\CourseRepository;
use App\Traits\ErrorResponse;
use App\Traits\PaginationParams;
use App\Traits\SuccessResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * @OA\Get(
 *     path="/api/cart",
 *     summary="List courses in authenticated user's cart with optional pagination",
 *     tags={"User Cart"},
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="PER_PAGE",
 *         in="query",
 *         description="Number of courses per page",
 *         required=false,
 *         @OA\Schema(
 *             type="integer",
 *             default=10
 *         )
 *     ),
 *     @OA\Parameter(
 *         name="ORDER_BY",
 *         in="query",
 *         description="Field to order the courses by",
 *         required=false,
 *         @OA\Schema(
 *             type="string",
 *             default="created_at"
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
 *         name="PAGE",
 *         in="query",
 *         description="Current page number for pagination",
 *         required=false,
 *         @OA\Schema(
 *             type="integer"
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
 *         description="Courses successfully retrieved for the authenticated user",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="Courses retrieved successfully"
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

class IndexCartCoursesController extends Controller
{
    use ErrorResponse, SuccessResponse, PaginationParams;

    public function __invoke(Request $request): JsonResponse
    {
        try {
            $paginationParams = $this->getAttributes($request);
            $courses = CourseRepository::indexCartCourses($paginationParams);
            return $this->returnSuccessResponse('Courses retrieved successfully', $courses, ResponseAlias::HTTP_OK);
        } catch (\Exception $e) {
            return $this->returnErrorResponse($e->getMessage(), ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    private function getAttributes(Request $request) : QueryConfig
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

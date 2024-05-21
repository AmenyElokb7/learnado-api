<?php

namespace App\Http\Controllers\Api\Course;

use App\Helpers\QueryConfig;
use App\Http\Controllers\Controller;
use App\Models\Course;
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
 *     path="/api/completed-courses",
 *     summary="List all completed courses for a user with optional filtering and pagination",
 *     tags={"Course"},
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="title",
 *         in="query",
 *         description="Filter courses by title",
 *         required=false,
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Parameter(
 *         name="perPage",
 *         in="query",
 *         description="Number of courses to return per page",
 *         required=false,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Parameter(
 *         name="orderBy",
 *         in="query",
 *         description="Field to order the courses by",
 *         required=false,
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Parameter(
 *         name="direction",
 *         in="query",
 *         description="Direction to order courses (asc or desc)",
 *         required=false,
 *         @OA\Schema(type="string", enum={"asc", "desc"})
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Courses listed successfully",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="Courses found"
 *             ),
 *
 *             @OA\Property(
 *                 property="pagination",
 *                 type="object",
 *                 @OA\Property(property="current_page", type="integer", example=1),
 *                 @OA\Property(property="per_page", type="integer", example=10),
 *                 @OA\Property(property="total_pages", type="integer", example=5),
 *                 @OA\Property(property="total_items", type="integer", example=50)
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
 *                 type="string",
 *                 example="An error occurred while processing your request."
 *             )
 *         )
 *     )
 * )
 */
class IndexCompletedCoursesController extends Controller
{
    /**
     * Handle the incoming request.
     */
    use SuccessResponse, ErrorResponse,PaginationParams;

    public function __invoke(Request $request) : JsonResponse
    {
        $paginationParams = $this->getAttributes($request);
        try {
            $completedCourses = CourseRepository::indexCompletedCourses($paginationParams);
            return $this->returnSuccessPaginationResponse(__('completed_courses_found'), $completedCourses, ResponseAlias::HTTP_OK, $paginationParams->isPaginated());
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

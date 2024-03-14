<?php

namespace App\Http\Controllers\Api\Course;

use App\Helpers\QueryConfig;
use App\Http\Controllers\Controller;
use App\Repositories\Course\CourseRepository;
use App\Traits\ErrorResponse;
use App\Traits\PaginationParams;
use App\Traits\SuccessResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * @OA\Get(
 *     path="/api/courses",
 *     summary="List all courses with optional filtering and pagination",
 *     tags={"Course"},
 *     @OA\Parameter(
 *         name="title",
 *         in="query",
 *         description="Filter courses by title",
 *         required=false,
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Parameter(
 *         name="category",
 *         in="query",
 *         description="Filter courses by category ID",
 *         required=false,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Parameter(
 *         name="language",
 *         in="query",
 *         description="Filter courses by language ID",
 *         required=false,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Parameter(
 *         name="is_paid",
 *         in="query",
 *         description="Filter courses by paid status",
 *         required=false,
 *         @OA\Schema(type="boolean")
 *     ),
 *     @OA\Parameter(
 *         name="price",
 *         in="query",
 *         description="Filter courses by price",
 *         required=false,
 *         @OA\Schema(type="number")
 *     ),
 *     @OA\Parameter(
 *         name="discount",
 *         in="query",
 *         description="Filter courses by discount",
 *         required=false,
 *         @OA\Schema(type="number")
 *     ),
 *     @OA\Parameter(
 *         name="teaching_type",
 *         in="query",
 *         description="Filter courses by teaching type ID",
 *         required=false,
 *         @OA\Schema(type="integer")
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
class IndexCoursesController extends Controller
{
    use ErrorResponse, SuccessResponse, PaginationParams;

    /**
     * @param Request $request
     * @return JsonResponse
     */

    public function __invoke(Request $request)
    {
        $paginationParams = $this->getAttributes($request);
        try {
            $courses = CourseRepository::index($paginationParams);
            return $this->returnSuccessPaginationResponse(__('course_found'), $courses, ResponseAlias::HTTP_OK, $paginationParams->isPaginated()
            );
        } catch (Exception $exception) {
            Log::error($exception->getMessage());
            return $this->returnErrorResponse($exception->getMessage() ?: __('general_error'), ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function getAttributes(Request $request): QueryConfig
    {
        $paginationParams = $this->getPaginationParams($request);

        $filters = [
            'title' => $request->input('title'),
            'category' => $request->input('category'),
            'language' => $request->input('language'),
            'is_paid' => $request->input('is_paid'),
            'price' => $request->input('price'),
            'discount' => $request->input('discount'),
            'teaching_type' => $request->input('teaching_type'),
        ];
        $search = new QueryConfig();
        $search->setFilters($filters)
            ->setPerPage($paginationParams['PER_PAGE'])
            ->setOrderBy($paginationParams['ORDER_BY'])
            ->setDirection($paginationParams['DIRECTION'])
            ->setPaginated($paginationParams['PAGINATION']);
        return $search;

    }
}

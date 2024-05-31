<?php

namespace App\Http\Controllers\Api\Course;

use App\Helpers\QueryConfig;
use App\Http\Controllers\Controller;
use App\Repositories\Course\CourseRepository;
use App\Traits\ErrorResponse;
use App\Traits\PaginationParams;
use App\Traits\SuccessResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

/**
 * @OA\Get(
 *     path="/api/course-certificate",
 *     summary="Retrieve course certificates",
 *     tags={"Course"},
 *     security={
 *         {"bearerAuth": {}}
 *     },
 *     @OA\Parameter(
 *         name="keyword",
 *         in="query",
 *         required=false,
 *         @OA\Schema(
 *             type="string",
 *             example="certificate"
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
 *         description="Course certificates retrieved successfully",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Course certificates found"),
 *             @OA\Property(property="data", type="array",
 *                 @OA\Items(
 *                     @OA\Property(property="id", type="integer", example=1),
 *                     @OA\Property(property="course_id", type="integer", example=1),
 *                     @OA\Property(property="certificate_name", type="string", example="Course Completion Certificate"),
 *                     @OA\Property(property="issued_at", type="string", format="date-time", example="2024-05-21T12:00:00Z"),
 *                     @OA\Property(property="created_at", type="string", format="date-time", example="2024-05-21T12:00:00Z"),
 *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-05-21T12:00:00Z")
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Invalid request due to incorrect input or missing fields"
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthorized - User not authorized to perform this action"
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Internal Server Error - Failed to retrieve course certificates"
 *     )
 * )
 */

class IndexCourseCertificatesController extends Controller
{
    /**
     * Handle the incoming request.
     */
    use SuccessResponse, ErrorResponse, PaginationParams;
    public function __invoke(Request $request): JsonResponse
    {
        $paginationParams = $this->getAttributes($request);
        try {
            $courseCertificates = CourseRepository::indexCourseCertificates($paginationParams);
            return $this->returnSuccessPaginationResponse(__('course_certificates_found'), $courseCertificates, 200, $paginationParams->isPaginated());
        } catch (\Exception $exception) {
            return $this->returnErrorResponse($exception->getMessage() ?: __('general_error'), 500);
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

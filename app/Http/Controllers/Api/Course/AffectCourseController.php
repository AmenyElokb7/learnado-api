<?php

namespace App\Http\Controllers\Api\Course;

use App\Http\Controllers\Controller;
use App\Repositories\Course\CourseRepository;
use App\Traits\ErrorResponse;
use App\Traits\SuccessResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * @OA\Post(
 *     path="/api/concepteur/affect-course/{course_id}/{facilitator_id}",
 *     summary="Assign a course to a facilitator",
 *     tags={"Course"},
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="course_id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *             format="int64"
 *         ),
 *         description="The ID of the course to be assigned"
 *     ),
 *     @OA\Parameter(
 *         name="facilitator_id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *             format="int64"
 *         ),
 *         description="The ID of the facilitator to whom the course will be assigned"
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Course assigned successfully",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Course affected successfully"),
 *             @OA\Property(property="data", type="object", description="Details of the course assignment")
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Bad request",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Missing required fields or invalid data")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Not Found",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Course or facilitator not found")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Internal Server Error",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="An error occurred while assigning the course")
 *         )
 *     )
 * )
 */
class AffectCourseController extends Controller
{
    protected $courseRepository;
    use SuccessResponse, ErrorResponse;

    public function __construct(CourseRepository $courseRepository)
    {
        $this->courseRepository = $courseRepository;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function __invoke(Request $request)
    {
        $course_id = $request->course_id;
        $facilitator_id = $request->facilitator_id;

        try {
            $course = $this->courseRepository->attachCourseToUser($course_id, $facilitator_id);
            return $this->returnSuccessResponse(__('course_assigned'), $course, ResponseAlias::HTTP_OK);

        } catch (Exception $e) {
            Log::error($e->getMessage());
            return $this->returnErrorResponse(__('general_error'), ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }

    }
}

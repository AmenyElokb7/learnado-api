<?php

namespace App\Http\Controllers\Api\Course;

use App\Http\Controllers\Controller;
use App\Repositories\Course\CourseRepository;
use App\Traits\ErrorResponse;
use App\Traits\SuccessResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
/**
 * @OA\Post(
 *     path="/api/subscribe-course/{id}",
 *     summary="Subscribe to a course",
 *     tags={"Course"},
 *     security={
 *         {"bearerAuth": {}}
 *     },
 *     @OA\Parameter(
 *         name="courseId",
 *         in="path",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *             example=1
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Course subscribed successfully",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Course subscribed successfully"),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="course_id", type="integer", example=1),
 *                 @OA\Property(property="user_id", type="integer", example=1),
 *                 @OA\Property(property="created_at", type="string", format="date-time", example="2024-05-21T12:00:00Z"),
 *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2024-05-21T12:00:00Z")
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
 *         description="Internal Server Error - Failed to subscribe to course"
 *     )
 * )
 */

class CourseSubscriptionController extends Controller
{
    use ErrorResponse, SuccessResponse;

    protected $courseRepository;

    public function __construct(CourseRepository $courseRepository)
    {
        $this->courseRepository = $courseRepository;
    }

    /**
     * @param $courseId
     * @return JsonResponse
     */

    public function __invoke($courseId): JsonResponse
    {
        $userId = auth()->user()->id;
        try {
            $course = $this->courseRepository->subscribeCourseToUsers($courseId, [$userId], false);
            return $this->returnSuccessResponse(__('course_subscribed'), $course, ResponseAlias::HTTP_OK);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return $this->returnErrorResponse($e->getMessage() ?: __('general_error'), $e->getCode() ?: ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

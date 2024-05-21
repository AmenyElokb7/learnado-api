<?php

namespace App\Http\Controllers\Api\Course;

use App\Http\Controllers\Controller;
use App\Repositories\Course\CourseRepository;
use App\Traits\ErrorResponse;
use App\Traits\SuccessResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
/**
 * @OA\Post(
 *     path="/api/complete-course/{course_id}",
 *     summary="Complete a course",
 *     tags={"Course"},
 *     security={
 *         {"bearerAuth": {}}
 *     },
 *     @OA\Parameter(
 *         name="course_id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *             example=1
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Course completed successfully",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Course completed successfully"),
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
 *         description="Internal Server Error - Failed to complete course"
 *     )
 * )
 */

class CompleteCourseController extends Controller
{
    /**
     * Handle the incoming request.
     * @param int $course_id
     * @return JsonResponse
     */
    use SuccessResponse, ErrorResponse;
    public function __invoke($course_id): JsonResponse
    {
        try
        {
            CourseRepository::completeCourse($course_id);
            return $this->returnSuccessResponse(__('course_completed'), null, ResponseAlias::HTTP_OK);
        }
        catch(\Exception $e)
        {
            Log::error($e->getMessage());
            return $this->returnErrorResponse(__('general_error'), ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

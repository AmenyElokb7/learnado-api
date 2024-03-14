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
 * @OA\Get(
 *     path="/api/designer/subscribed-users/{id}",
 *     summary="Get list of users subscribed to a course",
 *     tags={"Designer"},
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="course_id",
 *         in="path",
 *         required=true,
 *         description="The ID of the course to retrieve subscribed users for",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Subscribed users found successfully",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Subscribed users found"),
 *             @OA\Property(
 *                 property="users",
 *                 type="array",
 *                 @OA\Items(type="object",
 *                     @OA\Property(property="id", type="integer", example=1),
 *                     @OA\Property(property="name", type="string", example="John Doe"),
 *                     @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Course not found",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Course not found")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Internal Server Error",
 *         @OA\JsonContent(
 *             type="object",
 *     @OA\Property(property="message", type="string", example="An error occurred while processing your request.")
 *         )
 *     )
 * )
 */
class GetSubscribedUsersByCourseController extends Controller
{

    protected $courseRepository;
    use ErrorResponse, SuccessResponse;

    public function __construct(CourseRepository $courseRepository)
    {
        $this->courseRepository = $courseRepository;
    }

    /**
     * @param Request $request
     * @param int $course_id
     * @return JsonResponse
     */
    public function __invoke(Request $request, int $course_id): JsonResponse
    {
        try {
            $course = $this->courseRepository->getSubscribedUsersByCourse($course_id);
            return $this->returnSuccessResponse(__('subscribed_users_found'), $course, ResponseAlias::HTTP_OK);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return $this->returnErrorResponse($e->getMessage(), ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}

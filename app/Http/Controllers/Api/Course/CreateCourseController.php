<?php

namespace App\Http\Controllers\Api\Course;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateCourseRequest;
use App\Repositories\Course\CourseRepository;
use App\Traits\ErrorResponse;
use App\Traits\SuccessResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * @OA\Post(
 *     path="/api/designer/create-course",
 *     summary="Create a new course",
 *     tags={"Designer"},
 *     security={{"bearerAuth":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(
 *                 type="object",
 *                 required={"title", "category", "description", "language", "is_paid", "is_public"},
 *                 @OA\Property(property="title", type="string", example="Introduction to Laravel"),
 *                 @OA\Property(property="category", type="integer", description="Category ID", example=1),
 *                 @OA\Property(property="description", type="string", example="This course is an introduction to Laravel"),
 *                 @OA\Property(property="prerequisites", type="string", example="Basic knowledge of PHP"),
 *                 @OA\Property(property="course_for", type="string", example="Beginners"),
 *                 @OA\Property(property="language", type="integer", description="Language ID", example=1),
 *                 @OA\Property(property="duration", type="string", example="2 weeks"),
 *                 @OA\Property(property="is_paid", type="boolean", example=true),
 *                 @OA\Property(property="price", type="number", format="double", example=100.00),
 *                 @OA\Property(property="discount", type="number", format="double", example=10.00),
 *                 @OA\Property(property="facilitator_id", type="integer", example=1),
 *                 @OA\Property(property="is_public", type="boolean", example=true),
 *                 @OA\Property(property="selectedUserIds", type="array", @OA\Items(type="integer")),
 *                 @OA\Property(property="course_media", type="array", @OA\Items(type="string", format="binary")),
 *                 @OA\Property(property="teaching_type", type="integer", example=1),
 *                 @OA\Property(property="link", type="string", example="https://example.com/course"),
 *                 @OA\Property(property="start_time", type="string", format="date-time", example="2023-01-01T00:00:00Z"),
 *                 @OA\Property(property="end_time", type="string", format="date-time", example="2023-01-15T00:00:00Z"),
 *                 @OA\Property(property="latitude", type="string", example="40.712776"),
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Course created successfully",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Course created successfully"),
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Bad request",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Invalid request data")
 *         )
 *     ),
 * )
 */
class CreateCourseController extends Controller
{
    protected $courseRepository;
    use SuccessResponse, ErrorResponse;

    public function __construct(CourseRepository $courseRepository)
    {
        $this->courseRepository = $courseRepository;
    }

    /**
     * @param CreateCourseRequest $request
     * @return JsonResponse
     * @throws Exception
     */

    public function __invoke(CreateCourseRequest $request): JsonResponse
    {
        $data = $this->getAttributes($request);


        $course = $this->courseRepository->createCourse($data);
        if ($course) {
            return $this->returnSuccessResponse(__('course_created'), $course, ResponseAlias::HTTP_OK);
        } else {
            return $this->returnErrorResponse(__('user_not_authorized'), ResponseAlias::HTTP_FORBIDDEN);
        }

    }

    private function getAttributes(CreateCourseRequest $request): array
    {
        return $request->validated();
    }

}

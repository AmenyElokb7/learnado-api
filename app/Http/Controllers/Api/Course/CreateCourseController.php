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
 *                 required={"title", "category_id", "description", "language_id", "is_paid", "is_public"},
 *                 @OA\Property(property="title", type="string", example="Introduction to Laravel"),
 *                 @OA\Property(property="category_id", type="integer", description="Category ID", example=1),
 *                 @OA\Property(property="description", type="string", example="This course is an introduction to Laravel"),
 *                 @OA\Property(property="prerequisites", type="string", example="Basic knowledge of PHP"),
 *                 @OA\Property(property="course_for", type="string", example="Beginners"),
 *                 @OA\Property(property="language_id", type="integer", description="Language ID", example=1),
 *                 @OA\Property(property="duration", type="string", example="2 weeks"),
 *                 @OA\Property(property="is_paid", type="boolean", example=true),
 *                 @OA\Property(property="price", type="number", format="double", example=100.00),
 *                 @OA\Property(property="discount", type="number", format="double", example=10.00),
 *                 @OA\Property(property="facilitator_id", type="integer", example=1),
 *                 @OA\Property(property="is_public", type="boolean", example=true),
 *                 @OA\Property(property="selected_user_ids", type="array", @OA\Items(type="integer"), description="User IDs for whom the course is specifically created"),
 *                 @OA\Property(property="course_media", type="array", @OA\Items(type="string", format="binary"), description="Media files for the course"),
 *                 @OA\Property(property="teaching_type", type="integer", example=1),
 *                 @OA\Property(property="link", type="string", example="https://example.com/course"),
 *                 @OA\Property(property="start_time", type="string", format="date-time", example="2023-01-01T00:00:00Z"),
 *                 @OA\Property(property="end_time", type="string", format="date-time", example="2023-01-15T00:00:00Z"),
 *                 @OA\Property(property="latitude", type="string", example="40.712776"),
 *                 @OA\Property(property="longitude", type="string", example="-74.006058"),
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
    use SuccessResponse, ErrorResponse;

    /**
     * @param CreateCourseRequest $request
     * @return JsonResponse
     * @throws Exception
     */

    public function __invoke(CreateCourseRequest $request): JsonResponse
    {

        $data = $this->getAttributes($request);
        try {
            $course = CourseRepository::createCourse($data);
            return $this->returnSuccessResponse(__('course_created'), $course, ResponseAlias::HTTP_OK);

        } catch (Exception $e) {
            return $this->returnErrorResponse($e->getMessage() ?: __('general_error'), ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    private function getAttributes(CreateCourseRequest $request): array
    {
        return $request->validated();
    }

}

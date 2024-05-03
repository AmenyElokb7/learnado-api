<?php

namespace App\Http\Controllers\Api\Course;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateCourseRequest;
use App\Repositories\Course\CourseRepository;
use App\Traits\ErrorResponse;
use App\Traits\SuccessResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * @OA\Patch(
 *     path="/api/designer/update-course/{id}",
 *     summary="Update a course",
 *     tags={"Designer"},
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="course_id",
 *         in="path",
 *         required=true,
 *         description="The ID of the course to be updated",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(
 *                 type="object",
 *                 required={"title", "description", "category", "language"},
 *                 @OA\Property(property="title", type="string", description="Title of the course", example="Advanced Laravel"),
 *                 @OA\Property(property="description", type="string", description="Description of the course", example="This course covers advanced topics of Laravel."),
 *                 @OA\Property(property="category", type="integer", description="ID of the course category", example=2),
 *                 @OA\Property(property="language", type="integer", description="ID of the language", example=1),
 *                 @OA\Property(property="is_paid", type="boolean", description="Indicates if the course is paid", example=true),
 *                 @OA\Property(property="price", type="number", format="float", description="Price of the course (required if is_paid is true)", example=199.99),
 *                 @OA\Property(property="discount", type="number", format="float", description="Discount on the course", example=20),
 *                 @OA\Property(property="course_media", type="array", description="Array of course media files", @OA\Items(type="string", format="binary")),
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Course updated successfully",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Course updated successfully"),
 *
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Invalid request",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Invalid data provided")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Course not found",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="No course found with the provided ID")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Internal Server Error",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="An error occurred while processing your request.")
 *         )
 *     )
 * )
 */
class UpdateCourseController extends Controller
{
    protected $courseRepository;
    use SuccessResponse, ErrorResponse;

    public function __construct(CourseRepository $courseRepository)
    {
        $this->courseRepository = $courseRepository;
    }

    /**
     * @param UpdateCourseRequest $request
     * @param int $course_id
     * @return JsonResponse
     */

    public function __invoke(UpdateCourseRequest $request, int $course_id): JsonResponse
    {

        $data = $this->getAttributes($request);

        try {
            $course = $this->courseRepository->updateCourse($course_id, $data);
            return $this->returnSuccessResponse(__('course_updated'), $course, ResponseAlias::HTTP_OK);

        } catch (Exception $e) {
            Log::error($e->getMessage());
            return $this->returnErrorResponse($e->getMessage(), ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function getAttributes(UpdateCourseRequest $request): array
    {
        return $request->validated();
    }


}

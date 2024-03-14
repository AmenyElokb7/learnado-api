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
 * @OA\Get(
 *     path="/api/course/{id}",
 *     summary="Get course details by ID",
 *     tags={"Course"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="The ID of the course to retrieve",
 *         @OA\Schema(
 *             type="integer"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Course found successfully",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Course found"),
 *             @OA\Property(
 *                 property="course",
 *                 type="object",
 *                 description="The course object including details and associated media",
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="title", type="string", example="Introduction to Laravel"),
 *                 @OA\Property(property="description", type="string", example="A comprehensive course on Laravel framework"),
 *                 @OA\Property(property="created_at", type="string", format="date-time", example="2023-01-01T00:00:00Z"),
 *                 @OA\Property(
 *                     property="media",
 *                     type="array",
 *                     @OA\Items(type="object",
 *                         @OA\Property(property="id", type="integer", example=1),
 *                         @OA\Property(property="url", type="string", example="http://example.com/media/image1.jpg"),
 *                     )
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
 *             @OA\Property(property="message", type="string", example="An error occurred while processing your request.")
 *         )
 *     )
 * )
 */
class GetCourseByIdController extends Controller
{
    protected $courseRepository;
    use ErrorResponse, SuccessResponse;

    public function __construct(CourseRepository $courseRepository)
    {
        $this->courseRepository = $courseRepository;
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function __invoke($id): JsonResponse
    {
        try {
            $course = $this->courseRepository->getCourseWithMediaById($id);
            return $this->returnSuccessResponse(__('course_found'), $course, ResponseAlias::HTTP_OK);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return $this->returnErrorResponse(__('course_not_found'), ResponseAlias::HTTP_NOT_FOUND);
        }

    }
}

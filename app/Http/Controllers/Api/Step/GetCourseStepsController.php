<?php

namespace App\Http\Controllers\Api\Step;

use App\Http\Controllers\Controller;
use App\Repositories\Step\StepRepository;
use App\Traits\ErrorResponse;
use App\Traits\SuccessResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * @OA\Get(
 *     path="/api/designer/courses/{course_id}/steps",
 *     summary="Get all steps for a specific course",
 *     tags={"Designer"},
 *     @OA\Parameter(
 *         name="course_id",
 *         in="path",
 *         required=true,
 *         description="The ID of the course for which steps are being retrieved",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successfully retrieved steps for the course",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Steps found"),
 *             @OA\Property(
 *                 property="steps",
 *                 type="array",
 *                 @OA\Items(
 *                     type="object",
 *                     @OA\Property(property="id", type="integer", example=1),
 *                     @OA\Property(property="title", type="string", example="Introduction to Course"),
 *                     @OA\Property(property="description", type="string", example="An overview of the course content"),
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Course not found or no steps available",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Course not found or no steps available for the specified course")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Internal Server Error",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="An error occurred while processing your request")
 *         )
 *     )
 * )
 */
class GetCourseStepsController extends Controller
{

    use ErrorResponse, SuccessResponse;

    protected $stepRepository;

    public function __construct(StepRepository $stepRepository)
    {
        $this->stepRepository = $stepRepository;
    }

    /**
     * @param int $course_id
     * @return JsonResponse
     */
    public function __invoke(int $course_id): JsonResponse
    {
        try {
            $steps = $this->stepRepository->showCourseSteps($course_id);
            return $this->returnSuccessResponse(__('steps_found'), $steps, ResponseAlias::HTTP_OK);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return $this->returnErrorResponse(__('course_not_found'), ResponseAlias::HTTP_NOT_FOUND);
        }
    }
}

<?php

namespace App\Http\Controllers\Api\Step;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateStepRequest;
use App\Repositories\Step\StepRepository;
use App\Traits\ErrorResponse;
use App\Traits\SuccessResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * @OA\Post(
 *     path="/api/designer/create-step/{course_id}",
 *     summary="Create steps for a course",
 *     tags={"Designer"},
 *     @OA\Parameter(
 *         name="course_id",
 *         in="path",
 *         required=true,
 *         description="The ID of the course for which steps are being created",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         description="Data for creating new steps, including optional quizzes and media",
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(
 *                 type="object",
 *                 required={"steps"},
 *                 @OA\Property(
 *                     property="steps",
 *                     type="array",
 *                     @OA\Items(
 *                         type="object",
 *                         @OA\Property(property="title", type="string", example="Introduction to Course"),
 *                         @OA\Property(property="description", type="string", example="Course overview and objectives"),
 *                         @OA\Property(property="duration", type="integer", example=10),
 *                         @OA\Property(
 *                             property="media_files",
 *                             type="array",
 *                             description="Array of step-related media files",
 *                             @OA\Items(type="string", format="binary")
 *                         ),
 *                         @OA\Property(
 *                             property="media_urls",
 *                             type="array",
 *                             description="Array of URLs for step-related media",
 *                             @OA\Items(type="string", format="uri")
 *                         ),
 *                         @OA\Property(property="media_titles", type="string", example="Step Media Title"),
 *                         @OA\Property(
 *                             property="quiz",
 *                             type="object",
 *                             @OA\Property(property="title", type="string", example="Quiz 1"),
 *                             @OA\Property(
 *                                 property="questions",
 *                                 type="array",
 *                                 @OA\Items(
 *                                     type="object",
 *                                     @OA\Property(property="question", type="string", example="What is 2+2?"),
 *                                     @OA\Property(property="type", type="string", example="binary"),
 *                                     @OA\Property(property="is_valid", type="boolean", example=true),
 *                                     @OA\Property(
 *                                         property="answers",
 *                                         type="array",
 *                                         @OA\Items(
 *                                             type="object",
 *                                             @OA\Property(property="answer", type="string", example="4"),
 *                                             @OA\Property(property="is_valid", type="boolean", example=true)
 *                                         )
 *                                     )
 *                                 )
 *                             )
 *                         )
 *                     )
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Steps created successfully",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Steps created successfully"),
 *
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Invalid request data",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Invalid data provided")
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
class CreateStepController extends Controller
{

    protected $stepRepository;
    use ErrorResponse, SuccessResponse;

    public function __construct(StepRepository $stepRepository)
    {
        $this->stepRepository = $stepRepository;
    }

    public function __invoke(CreateStepRequest $request, $course_id): JsonResponse
    {
        try {
            $data = $this->getAttributes($request);
            $step = $this->stepRepository->createStep($data, $course_id);
            return $this->returnSuccessResponse(__('step_created'), $step, ResponseAlias::HTTP_CREATED);
        } catch (Exception $e) {
            return $this->returnErrorResponse($e->getMessage(), ResponseAlias::HTTP_BAD_REQUEST);

        }
    }

    private function getAttributes(CreateStepRequest $request): array
    {
        return $request->validated();
    }
}

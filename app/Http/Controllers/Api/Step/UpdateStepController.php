<?php

namespace App\Http\Controllers\Api\Step;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateStepRequest;
use App\Repositories\Step\StepRepository;
use App\Traits\ErrorResponse;
use App\Traits\SuccessResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * @OA\Patch(
 *     path="/api/designer/update-steps/{step_id}",
 *     summary="Update details of a specific step",
 *     tags={"Designer"},
 *     @OA\Parameter(
 *         name="step_id",
 *         in="path",
 *         required=true,
 *         description="The ID of the step to update",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         description="Step update data including optional fields for media management",
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(
 *                 type="object",
 *                 @OA\Property(property="title", type="string", description="Updated title of the step", example="Step 1: Introduction"),
 *                 @OA\Property(property="description", type="string", description="Updated description of the step", example="An introductory step"),
 *                 @OA\Property(property="duration", type="integer", description="Updated duration in minutes", example=10),
 *                 @OA\Property(
 *                     property="media_files",
 *                     type="array",
 *                     description="Array of new media files to upload",
 *                     @OA\Items(type="string", format="binary")
 *                 ),
 *                 @OA\Property(
 *                     property="media_to_remove",
 *                     type="array",
 *                     description="IDs of media to remove from the step",
 *                     @OA\Items(type="integer")
 *                 ),
 *                 @OA\Property(
 *                     property="media_urls",
 *                     type="array",
 *                     description="URLs of new media to be associated with the step",
 *                     @OA\Items(type="string", format="uri")
 *                 ),
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Step updated successfully",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Step updated successfully"),
 *             @OA\Property(
 *                 property="step",
 *                 type="object",
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="title", type="string", example="Step 1: Introduction"),
 *                 @OA\Property(property="description", type="string", example="An introductory step"),
 *                 @OA\Property(property="duration", type="integer", example=10),
 *                 @OA\Property(
 *                     property="media",
 *                     type="array",
 *                     @OA\Items(
 *                         type="object",
 *                         @OA\Property(property="id", type="integer", example=1),
 *                         @OA\Property(property="url", type="string", example="http://example.com/media/intro.jpg"),
 *                         @OA\Property(property="title", type="string", example="Introductory Image")
 *                     )
 *                 ),
 *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2023-01-01T00:00:00Z")
 *             )
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
 *         response=404,
 *         description="Step not found",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Step not found")
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
class UpdateStepController extends Controller
{

    /**
     * @var StepRepository
     */
    protected $stepRepository;
    use ErrorResponse, SuccessResponse;

    public function __construct(StepRepository $stepRepository)
    {
        $this->stepRepository = $stepRepository;
    }

    /**
     * @param UpdateStepRequest $request
     * @param $step_id
     * @return JsonResponse
     */
    public function __invoke(UpdateStepRequest $request, $step_id): JsonResponse
    {
        $data = $this->getAttributes($request);
        try {
            $step = $this->stepRepository->updateStep($step_id, $data);
            return $this->returnSuccessResponse(__('step_updated'), $step, ResponseAlias::HTTP_CREATED);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return $this->returnErrorResponse($e->getMessage(), ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function getAttributes(UpdateStepRequest $request): array
    {
        return $request->validated();
    }
}

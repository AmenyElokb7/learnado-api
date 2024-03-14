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
 * @OA\Delete(
 *     path="/api/designer/delete-step/{step_id}",
 *     summary="Delete a step from a course",
 *     tags={"Designer"},
 *     @OA\Parameter(
 *         name="stepId",
 *         in="path",
 *         required=true,
 *         description="The ID of the step to be deleted",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Step deleted successfully",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Step deleted successfully")
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Bad Request - Incorrect step ID or other request error",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Invalid request data")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Not Found - Step not found",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Step not found")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Internal Server Error - Error deleting step",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="An error occurred while processing your request.")
 *         )
 *     )
 * )
 */
class DeleteStepController extends Controller
{
    use ErrorResponse, SuccessResponse;

    protected $stepRepository;

    public function __construct(StepRepository $stepRepository)
    {
        $this->stepRepository = $stepRepository;
    }

    /**
     * delete step with its relations from the course
     * @param $stepId
     * @return JsonResponse
     */

    public function __invoke($stepId)
    {
        try {
            $this->stepRepository->deleteStepFromCourse($stepId);
            return $this->returnSuccessResponse(__('step_deleted'), [], ResponseAlias::HTTP_OK);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return $this->returnErrorResponse($e->getMessage(), ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

<?php

namespace App\Http\Controllers\Api\Quiz;

use App\Http\Controllers\Controller;
use App\Repositories\Quiz\QuizRepository;
use App\Traits\ErrorResponse;
use App\Traits\SuccessResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * @OA\Delete(
 *     path="/api/designer/delete-quiz/{step_id}",
 *     summary="Delete a quiz from a specific step",
 *     tags={"Designer"},
 *     @OA\Parameter(
 *         name="stepId",
 *         in="path",
 *         required=true,
 *         description="The ID of the step from which the quiz will be deleted",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Quiz deleted successfully",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Quiz deleted successfully")
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
 *         description="Not Found - Step or quiz not found",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Step or quiz not found")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Internal Server Error - Error deleting quiz",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="An error occurred while processing your request.")
 *         )
 *     )
 * )
 */
class DeleteQuizController extends Controller
{

    use SuccessResponse, ErrorResponse;

    public function __invoke($quiz_id): JsonResponse
    {
        try {
            QuizRepository::deleteQuiz($quiz_id);
            return $this->returnSuccessResponse(__('quiz_deleted'), null, ResponseAlias::HTTP_OK);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return $this->returnErrorResponse($e->getMessage() ?: __('general_error'), $e->getCode() ?: ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }

    }
}

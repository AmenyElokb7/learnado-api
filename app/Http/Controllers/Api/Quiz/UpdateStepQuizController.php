<?php

namespace App\Http\Controllers\Api\Quiz;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateQuizRequest;
use App\Repositories\Quiz\QuizRepository;
use App\Repositories\Step\StepRepository;
use App\Traits\ErrorResponse;
use App\Traits\SuccessResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * @OA\Patch(
 *     path="/api/designer/update-quiz/{step_id}",
 *     summary="Update a quiz for a specific step",
 *     tags={"Designer"},
 *     @OA\Parameter(
 *         name="stepId",
 *         in="path",
 *         required=true,
 *         description="The ID of the step associated with the quiz to be updated",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         description="Quiz update data including questions and answers",
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                 type="object",
 *                 @OA\Property(
 *                     property="quiz",
 *                     type="object",
 *                     @OA\Property(property="title", type="string", example="Updated Quiz Title"),
 *                     @OA\Property(
 *                         property="questions",
 *                         type="array",
 *                         @OA\Items(
 *                             type="object",
 *                             @OA\Property(property="id", type="integer", example=1),
 *                             @OA\Property(property="question", type="string", example="What is Laravel?"),
 *                             @OA\Property(property="type", type="string", example="QCM"),
 *                             @OA\Property(property="is_valid", type="boolean", example=true),
 *                             @OA\Property(
 *                                 property="answers",
 *                                 type="array",
 *                                 @OA\Items(
 *                                     type="object",
 *                                     @OA\Property(property="id", type="integer", example=1),
 *                                     @OA\Property(property="answer", type="string", example="A PHP Framework"),
 *                                     @OA\Property(property="is_valid", type="boolean", example=true)
 *                                 )
 *                             )
 *                         )
 *                     ),
 *                     @OA\Property(
 *                         property="questions_to_remove",
 *                         type="array",
 *                         @OA\Items(type="integer", example=2)
 *                     ),
 *                     @OA\Property(
 *                         property="answers_to_remove",
 *                         type="array",
 *                         @OA\Items(type="integer", example=3)
 *                     )
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Quiz updated successfully",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Quiz updated successfully"),
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
 *         response=404,
 *         description="Step or quiz not found",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Step or quiz not found")
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
class UpdateStepQuizController extends Controller
{

    protected $quizRepository;

    use ErrorResponse, SuccessResponse;

    public function __construct(QuizRepository $quizRepository)
    {
        $this->quizRepository = $quizRepository;
    }

    /**
     * @param $step_id
     * @param UpdateQuizRequest $request
     * @return JsonResponse
     * @throws Exception
     */
    public function __invoke($step_id, UpdateQuizRequest $request): JsonResponse
    {
        $data = $this->getAttributes($request);


        $quiz = $this->quizRepository->updateQuiz($step_id, $data);

        return $this->returnSuccessResponse(__('quiz_updated'), $quiz, ResponseAlias::HTTP_CREATED);

    }

    private function getAttributes(UpdateQuizRequest $request): array
    {
        return $request->validated();
    }
}

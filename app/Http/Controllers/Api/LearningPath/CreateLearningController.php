<?php

namespace App\Http\Controllers\Api\LearningPath;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateLearningPathRequest;
use App\Repositories\LearningPath\LearningPathRepository;
use App\Traits\ErrorResponse;
use App\Traits\SuccessResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class CreateLearningController extends Controller
{

    use SuccessResponse, ErrorResponse;

    protected $learningPathRepository;

    public function __construct(LearningPathRepository $learningPathRepository)
    {
        $this->learningPathRepository = $learningPathRepository;
    }

    /**
     * @param CreateLearningPathRequest $request
     * @return JsonResponse
     */

    public function __invoke(CreateLearningPathRequest $request): JsonResponse
    {

        $data = $request->validated();

        try {
            $learningPath = $this->learningPathRepository->createLearningPath($data);
            return $this->returnSuccessResponse('Learning path created successfully', $learningPath, ResponseAlias::HTTP_CREATED);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return $this->returnErrorResponse($e->getMessage(), ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }


    }
}

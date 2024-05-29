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

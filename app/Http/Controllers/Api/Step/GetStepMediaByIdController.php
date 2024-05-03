<?php

namespace App\Http\Controllers\Api\Step;

use App\Http\Controllers\Controller;
use App\Repositories\Step\StepRepository;
use App\Traits\ErrorResponse;
use App\Traits\SuccessResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class GetStepMediaByIdController extends Controller
{
    /**
     * Handle the incoming request.
     */
    use SuccessResponse, ErrorResponse;

    protected $stepMediaRepository;

    public function __construct(StepRepository $stepMediaRepository)
    {
        $this->stepMediaRepository = $stepMediaRepository;
    }

    public function __invoke($id)
    {
        try{
            $stepMedia = $this->stepMediaRepository->getStepMediaById($id);
            return $this->returnSuccessResponse( __('step_retrieved_successfully'), $stepMedia, ResponseAlias::HTTP_OK);
        }catch(\Exception $e){
            return $this->returnErrorResponse($e->getMessage(), ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

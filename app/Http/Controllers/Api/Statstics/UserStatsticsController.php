<?php

namespace App\Http\Controllers\Api\Statstics;

use App\Http\Controllers\Controller;
use App\Repositories\Statistics\StatsticsRepository;
use App\Traits\ErrorResponse;
use App\Traits\SuccessResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class UserStatsticsController extends Controller
{
    /**
     * Handle the incoming request.
     */
    use SuccessResponse, ErrorResponse;
    public function __invoke(Request $request) : JsonResponse
    {
        try {
            $statistics = StatsticsRepository::getUserStatistics();
            return $this->returnSuccessResponse(__('statistics_found'), $statistics, ResponseAlias::HTTP_OK);
        } catch (\Exception $exception) {
            return $this->returnErrorResponse($exception->getMessage() ?: __('general_error'), ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

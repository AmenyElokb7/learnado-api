<?php

namespace App\Http\Controllers\Api\Statstics;

use App\Http\Controllers\Controller;
use App\Repositories\Statistics\StatsticsRepository;
use App\Traits\ErrorResponse;
use App\Traits\SuccessResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class GuestStatisticsController extends Controller
{
    use SuccessResponse,ErrorResponse;
    public function __invoke(Request $request) : JsonResponse
    {
        try {
            $statistics = StatsticsRepository::getGuestStatistics();
            return $this->returnSuccessResponse(__('statistics_found'), $statistics, ResponseAlias::HTTP_OK);
        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
            return $this->returnErrorResponse($exception->getMessage() ?: __('general_error'), ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

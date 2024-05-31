<?php

namespace App\Http\Controllers\Api\Stripe;

use App\Helpers\QueryConfig;
use App\Http\Controllers\Controller;
use App\Repositories\Payment\PaymentRepository;
use App\Traits\ErrorResponse;
use App\Traits\PaginationParams;
use App\Traits\SuccessResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class IndexInvoicesController extends Controller
{
    /**
     * Handle the incoming request.
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    use SuccessResponse, ErrorResponse,PaginationParams;
    public function __invoke(Request $request): JsonResponse
    {
        $paginationParams = $this->getAttributes($request);
        try{
            $invoices = PaymentRepository::indexUserInvoices($paginationParams);
            return $this->returnSuccessPaginationResponse(__('invoices_found'), $invoices, ResponseAlias::HTTP_OK, $paginationParams->isPaginated());
        }
        catch (Exception $exception) {
            Log::error($exception->getMessage());
            return $this->returnErrorResponse($exception->getMessage() ?: __('general_error'), ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param Request $request
     * @return QueryConfig
     */
    private function getAttributes(Request $request): QueryConfig
    {
        $paginationParams = $this->getPaginationParams($request);
        $search = new QueryConfig();
        $search->setPerPage($paginationParams['PER_PAGE'])
            ->setOrderBy($paginationParams['ORDER_BY'])
            ->setDirection($paginationParams['DIRECTION'])
            ->setPaginated($paginationParams['PAGINATION'])
            ->setPage($paginationParams['PAGE']);
        return $search;

    }
}

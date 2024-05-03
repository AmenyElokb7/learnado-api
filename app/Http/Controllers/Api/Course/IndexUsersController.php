<?php

namespace App\Http\Controllers\Api\Course;

use App\Enum\UserRoleEnum;
use App\Helpers\QueryConfig;
use App\Http\Controllers\Controller;
use App\Repositories\User\UserRepository;
use App\Traits\ErrorResponse;
use App\Traits\PaginationParams;
use App\Traits\SuccessResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class IndexUsersController extends Controller
{
    use ErrorResponse, SuccessResponse, PaginationParams;


    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
    {
        $paginationParams = $this->getAttributes($request);
        try {
            $users = UserRepository::index($paginationParams);
            return $this->returnSuccessPaginationResponse(__('user_retrieved'), $users, ResponseAlias::HTTP_OK, $paginationParams->isPaginated());

        } catch (Exception $exception) {
            Log::error($exception->getMessage());
            return $this->returnErrorResponse(__('messages.general_error'), ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param Request $request
     * @return QueryConfig
     */
    private function getAttributes(Request $request): QueryConfig
    {
        $paginationParams = $this->getPaginationParams($request);

        $filters = [
            'role' => [UserRoleEnum::USER],
            'is_valid' => 1,
        ];
        $search = new QueryConfig();
        $search->setFilters($filters)
            ->setPerPage($paginationParams['PER_PAGE'])
            ->setOrderBy($paginationParams['ORDER_BY'])
            ->setDirection($paginationParams['DIRECTION'])
            ->setPaginated($paginationParams['PAGINATION'])
            ->setPage($paginationParams['PAGE']);
        return $search;
    }
}


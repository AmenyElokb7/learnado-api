<?php

namespace App\Http\Controllers\Api\User;

use App\Helpers\QueryConfig;
use App\Http\Controllers\Controller;
use App\Repositories\User\UserRepository;
use App\Traits\ErrorResponse;
use App\Traits\PaginationParams;
use App\Traits\SuccessResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * @OA\Get(
 *     path="/api/admin/users",
 *     summary="Get a list of users",
 *     tags={"Admin"},
 *     @OA\Parameter(
 *         name="first_name",
 *         in="query",
 *         description="Filter users by first name",
 *         required=false,
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Parameter(
 *         name="last_name",
 *         in="query",
 *         description="Filter users by last name",
 *         required=false,
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Parameter(
 *         name="email",
 *         in="query",
 *         description="Filter users by email",
 *         required=false,
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Parameter(
 *         name="PER_PAGE",
 *         in="query",
 *         description="Number of users per page",
 *         required=false,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Parameter(
 *         name="ORDER_BY",
 *         in="query",
 *         description="Attribute to order users by",
 *         required=false,
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Parameter(
 *         name="DIRECTION",
 *         in="query",
 *         description="Direction of sorting, asc or desc",
 *         required=false,
 *         @OA\Schema(type="string", enum={"asc", "desc"})
 *     ),
 *     @OA\Parameter(
 *         name="PAGINATION",
 *         in="query",
 *         description="Enable pagination (true or false)",
 *         required=false,
 *         @OA\Schema(type="boolean")
 *     ),
 *     @OA\Response(response=200, description="Successful operation",
 *     content={
 *          @OA\MediaType(
 *          mediaType="application/json",
 *                ),
 *            }
 *     ),
 *     @OA\Response(response=400, description="Invalid request")
 * )
 */
class IndexUsersController extends Controller
{
    use ErrorResponse, SuccessResponse, PaginationParams;

    public function __invoke(Request $request): JsonResponse
    {
        $paginationParams = $this->getAttributes($request);
        try {
            $users = UserRepository::index($paginationParams);
            return $this->returnSuccessPaginationResponse(
                __('user_retrieved'),
                $users,
                ResponseAlias::HTTP_OK,
                $paginationParams->isPaginated()
            );
        } catch (Exception $exception) {
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
            'first_name' => $request->input('first_name'),
            'last_name' => $request->input('last_name'),
            'email' => $request->input('email'),
            'role' => $request->input('role'),
        ];
        $search = new QueryConfig();
        $search->setFilters($filters)
            ->setPerPage($paginationParams['PER_PAGE'])
            ->setOrderBy($paginationParams['ORDER_BY'])
            ->setDirection($paginationParams['DIRECTION'])
            ->setPaginated($paginationParams['PAGINATION']);
        return $search;

    }


}

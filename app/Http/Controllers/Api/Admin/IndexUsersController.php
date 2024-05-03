<?php

namespace App\Http\Controllers\Api\Admin;

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
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * @OA\Get(
 *     path="/api/admin/users",
 *     summary="Get a list of users",
 *     tags={"Admin"},
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="keyword",
 *         in="query",
 *         description="Filter users by keyword",
 *         required=false,
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Parameter(
 *         name="role",
 *         in="query",
 *         description="Filter users by role; multiple roles can be specified as comma-separated values (e.g., 1,2)",
 *         required=false,
 *     ),
 *     @OA\Parameter(
 *         name="PER_PAGE",
 *         in="query",
 *         description="Number of users per page",
 *         required=false,
 *         @OA\Schema(type="integer", default=10)
 *     ),
 *     @OA\Parameter(
 *         name="ORDER_BY",
 *         in="query",
 *         description="Attribute to order users by",
 *         required=false,
 *         @OA\Schema(type="string", example="created_at")
 *     ),
 *     @OA\Parameter(
 *         name="DIRECTION",
 *         in="query",
 *         description="Direction of sorting, asc or desc",
 *         required=false,
 *         @OA\Schema(type="string", enum={"asc", "desc"}, default="asc")
 *     ),
 *     @OA\Parameter(
 *         name="PAGINATION",
 *         in="query",
 *         description="Enable pagination (true or false)",
 *         required=false,
 *         @OA\Schema(type="boolean", default=true)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="Users retrieved successfully"
 *             ),
 *             @OA\Property(
 *                 property="data",
 *                 type="array",
 *                 @OA\Items(
 *                @OA\Property(property="id", type="integer", example=1),
 *           @OA\Property(property="first_name", type="string", example="John"),
 *      @OA\Property(property="last_name", type="string", example="Doe"),
 *     @OA\Property(property="email", type="string", example="JohnDoe@example.com"),
 *     @OA\Property(property="role", type="integer", example=1),
 *     @OA\Property(property="profile_picture", type="string", example="url_to_picture"),
 *        ),
 *        ),
 *             ),
 *             @OA\Property(
 *                 property="pagination",
 *                 type="object",
 *                 @OA\Property(property="total", type="integer", example=50),
 *                 @OA\Property(property="count", type="integer", example=10),
 *                 @OA\Property(property="per_page", type="integer", example=10),
 *                 @OA\Property(property="current_page", type="integer", example=1),
 *                 @OA\Property(property="total_pages", type="integer", example=5)
 *             )
 *         )
 *     ),
 *    @OA\Response(
 *          response=400,
 *          description="Bad request - The request could not be understood by the server due to malformed syntax."
 *      ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthorized - User not authorized to perform this action"
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Internal Server Error - Failed to retrieve user list"
 *     )
 * )
 */


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
            return $this->returnSuccessPaginationResponse(
                __('user_retrieved'),
                $users,
                ResponseAlias::HTTP_OK,
                $paginationParams->isPaginated()
            );
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
        $role = $request->input('role', [
            UserRoleEnum::USER->value,
            UserRoleEnum::DESIGNER->value,
            UserRoleEnum::FACILITATOR->value
        ]);
        $role = is_array($role) ? $role : explode(',', $role);

        $filters = [
            'keyword' => $request->input('keyword', null),
            'role' => $role,
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

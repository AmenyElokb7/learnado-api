<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enum\UserRoleEnum;
use App\Helpers\QueryConfig;
use App\Http\Controllers\Controller;
use App\Repositories\User\UserRepository;
use App\Traits\ErrorResponse;
use App\Traits\PaginationParams;
use App\Traits\SuccessResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
/**
 * @OA\Get(
 *     path="/api/admin/accepted-users",
 *     operationId="getAcceptedUsers",
 *     tags={"Admin"},
 *     summary="List accepted users with optional pagination and filters",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="keyword",
 *         in="query",
 *         required=false,
 *         description="Search keyword for user details",
 *         @OA\Schema(
 *             type="string"
 *         )
 *     ),
 *     @OA\Parameter(
 *         name="PER_PAGE",
 *         in="query",
 *         description="Number of users to return per page",
 *         required=false,
 *         @OA\Schema(
 *             type="integer",
 *             default=10
 *         )
 *     ),
 *     @OA\Parameter(
 *         name="ORDER_BY",
 *         in="query",
 *         description="Column to order the results by",
 *         required=false,
 *         @OA\Schema(
 *             type="string",
 *             default="created_at"
 *         )
 *     ),
 *     @OA\Parameter(
 *         name="DIRECTION",
 *         in="query",
 *         description="Direction to order users",
 *         required=false,
 *         @OA\Schema(
 *             type="string",
 *             default="asc",
 *             enum={"asc", "desc"}
 *         )
 *     ),
 *     @OA\Parameter(
 *         name="PAGINATION",
 *         in="query",
 *         description="Boolean to specify if pagination is enabled",
 *         required=false,
 *         @OA\Schema(
 *             type="boolean",
 *             default=true
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful retrieval of users",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="Users retrieved successfully"
 *             ),
 *            @OA\Property(
 *            property="data",
 *       type="array",
 *     @OA\Items(
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="first_name", type="string", example="John"),
 *     @OA\Property(property="last_name", type="string", example="Doe"),
 *     @OA\Property(property="email", type="string", example="JohnDoe@example.com"),
 *     @OA\Property(property="role", type="integer", example=1),
 *     @OA\Property(property="profile_picture", type="string", example="url_to_picture"),
 *     @OA\Property(property="is_valid", type="integer", example=1),
 *     @OA\Property(property="created_at", type="string", example="2021-01-01T00:00:00.000000Z"),
 *     @OA\Property(property="updated_at", type="string", example="2021-01-01T00:00:00.000000Z")
 *    )
 * ),
 *
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
 *     @OA\Response(
 *         response=400,
 *         description="Invalid request",
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthorized",
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Internal server error",
 *     )
 * )
 */

class IndexAcceptedUsersController extends Controller
{
    use ErrorResponse, SuccessResponse, PaginationParams;

    public function __invoke(Request $request) : JsonResponse
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
        } catch (\Exception $exception) {
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
            'keyword' => $request->input('keyword', null),
            'role' => [UserRoleEnum::USER->value, UserRoleEnum::DESIGNER->value, UserRoleEnum::FACILITATOR->value],
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


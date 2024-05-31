<?php

namespace App\Http\Controllers\Api\Admin;

use App\Helpers\QueryConfig;
use App\Http\Controllers\Controller;
use App\Repositories\Message\MessageRepository;
use App\Traits\ErrorResponse;
use App\Traits\PaginationParams;
use App\Traits\SuccessResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * @OA\Get(
 *     path="/api/admin/notifications",
 *     summary="Retrieve admin notifications",
 *     tags={"Admin"},
 *     security={
 *         {"bearerAuth": {}}
 *     },
 *     @OA\Parameter(
 *         name="keyword",
 *         in="query",
 *         required=false,
 *         @OA\Schema(
 *             type="string",
 *             example="important"
 *         )
 *     ),
 *     @OA\Parameter(
 *         name="page",
 *         in="query",
 *         required=false,
 *         @OA\Schema(
 *             type="integer",
 *             example=1
 *         )
 *     ),
 *     @OA\Parameter(
 *         name="per_page",
 *         in="query",
 *         required=false,
 *         @OA\Schema(
 *             type="integer",
 *             example=10
 *         )
 *     ),
 *     @OA\Parameter(
 *         name="order_by",
 *         in="query",
 *         required=false,
 *         @OA\Schema(
 *             type="string",
 *             example="created_at"
 *         )
 *     ),
 *     @OA\Parameter(
 *         name="direction",
 *         in="query",
 *         required=false,
 *         @OA\Schema(
 *             type="string",
 *             example="desc"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Notifications retrieved successfully",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Notifications found"),
 *             @OA\Property(property="data", type="array",
 *                 @OA\Items(
 *                     @OA\Property(property="id", type="integer", example=1),
 *                     @OA\Property(property="subject", type="string", example="New User Registration"),
 *                     @OA\Property(property="body", type="string", example="A new user has registered."),
 *                     @OA\Property(property="is_read", type="boolean", example=false),
 *                     @OA\Property(property="created_at", type="string", format="date-time", example="2024-05-21T12:00:00Z"),
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Internal Server Error - Failed to retrieve notifications"
 *     )
 * )
 */


class AdminNotificationController extends Controller
{
    /**
     * Handle the incoming request.
     */
    use ErrorResponse,SuccessResponse, PaginationParams;
    protected $messageRepository;
    public function __construct(MessageRepository $messageRepository)
    {
        $this->messageRepository = $messageRepository;
    }
    public function __invoke(Request $request): JsonResponse
    {
        $query = $this->getAttributes($request);
        try {
            $messages = $this->messageRepository->indexAdminNotifications($query);
            return $this->returnSuccessResponse(__('messages.found'), $messages, ResponseAlias::HTTP_OK);
        } catch (\Exception $e) {
            return $this->returnErrorResponse($e->getMessage(), ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    private function getAttributes(Request $request): QueryConfig
    {
        $paginationParams = $this->getPaginationParams($request);
        $search = new QueryConfig();
        $filters = [
            'subject ' => $request->input('keyword'),
            'is_read' => false,
        ];
        $search->setFilters($filters)
            ->setPerPage($paginationParams['PER_PAGE'])
            ->setOrderBy($paginationParams['ORDER_BY'])
            ->setDirection($paginationParams['DIRECTION'])
            ->setPaginated($paginationParams['PAGINATION'])
            ->setPage($paginationParams['PAGE']);
        return $search;

    }
}

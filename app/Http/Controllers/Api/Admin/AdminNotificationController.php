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
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

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

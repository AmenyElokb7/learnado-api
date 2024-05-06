<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\Message\MessageRepository;
use App\Traits\ErrorResponse;
use App\Traits\SuccessResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class MarkAsReadNotificationController extends Controller
{
    /**
     * Handle the incoming request.
     */
    use SuccessResponse,ErrorResponse;
    protected $messageRepository;

    public function __construct(MessageRepository $messageRepository)
    {
        $this->messageRepository = $messageRepository;
    }

    /**
     * @param $messageId
     * @return JsonResponse
     */
    public function __invoke($messageId): JsonResponse
    {
        try {
            $this->messageRepository->MarkAsRead($messageId);
            return $this->returnSuccessResponse(__('messages.marked_as_read'), null, ResponseAlias::HTTP_OK);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return $this->returnErrorResponse($e->getMessage() ?: __('general_error'), $e->getCode() ?:ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

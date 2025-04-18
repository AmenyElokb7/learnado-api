<?php

namespace App\Http\Controllers\Api\Message;

use App\Http\Controllers\Controller;
use App\Repositories\Message\MessageRepository;
use App\Traits\ErrorResponse;
use App\Traits\SuccessResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class IndexPrivateMessageController extends Controller
{
    /**
     * Handle the incoming request.
     */
    use SuccessResponse,ErrorResponse;
    public function __invoke(Request $request) : JsonResponse
    {
        try
        {
            $message = MessageRepository::indexPrivateMessages();
            return$this->returnSuccessResponse(__('message_retrieved_successfully'),$message, ResponseAlias::HTTP_OK);
        }
        catch (\Exception $e) {
            Log::error($e->getMessage());
            return $this->returnErrorResponse($e->getMessage(), ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

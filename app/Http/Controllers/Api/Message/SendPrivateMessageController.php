<?php

namespace App\Http\Controllers\Api\Message;

use App\Http\Controllers\Controller;
use App\Repositories\Message\MessageRepository;
use App\Traits\ErrorResponse;
use App\Traits\SuccessResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class SendPrivateMessageController extends Controller
{
    /**
     * Handle the incoming request.
     */
    use SuccessResponse,ErrorResponse;
    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->all();

        try{
            $message =MessageRepository::sendPrivateMessage($data['message'], $data['receiver_id']);
            return $this->returnSuccessResponse(__('message_sent'), $message, ResponseAlias::HTTP_OK);
        }catch (\Exception $exception){
            return $this->returnErrorResponse($exception->getMessage() ?: __('general_error'), ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

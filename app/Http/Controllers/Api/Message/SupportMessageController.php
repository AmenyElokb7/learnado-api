<?php

namespace App\Http\Controllers\Api\Message;

use App\Http\Controllers\Controller;
use App\Http\Requests\SupportMessagesRequest;
use App\Repositories\Message\MessageRepository;
use App\Traits\ErrorResponse;
use App\Traits\SuccessResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class SupportMessageController extends Controller
{
    protected $messageRepository;

    public function __construct(MessageRepository $messageRepository)
    {
        $this->messageRepository = $messageRepository;
    }
    use ErrorResponse, SuccessResponse;
    /**
     * Handle the incoming request.
     */
    public function __invoke(SupportMessagesRequest $request) : JsonResponse
    {
        $data = $request->validated();
        try{
            $message = $this->messageRepository->saveMessage(auth()->id(), $data);
            return $this->returnSuccessResponse(__('message_sent'), $message,ResponseAlias::HTTP_CREATED);
        }
        catch(\Exception $e){
            return $this->returnErrorResponse($e->getMessage(), ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

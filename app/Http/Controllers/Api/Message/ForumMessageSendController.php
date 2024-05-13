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

class ForumMessageSendController extends Controller
{
    /**
     * Handle the incoming request.
     * @param Request $request
     * @return JsonResponse
     */
    use ErrorResponse, SuccessResponse;
    public function __invoke(Request $request) : JsonResponse
    {
        $data = $request->all();
        try {
            $message =MessageRepository::forumMessageSend($data['message'], $data['learning_path_id'] , $data['course_id']);
            return $this->returnSuccessResponse('Message sent successfully',$message, ResponseAlias::HTTP_OK);
        }catch (\Exception $e){
            Log::error($e->getMessage());
            return $this->returnErrorResponse('Failed to send message', ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

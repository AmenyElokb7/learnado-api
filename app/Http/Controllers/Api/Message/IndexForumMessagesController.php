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

class IndexForumMessagesController extends Controller
{
    /**
     * Handle the incoming request.
     */
    use ErrorResponse, SuccessResponse;
    public function __invoke($courseId=null, $learningPathId=null): JsonResponse
    {
        try{
            $messages =MessageRepository::indexForumMessages($courseId);
            return $this->returnSuccessResponse('Messages fetched successfully',$messages, ResponseAlias::HTTP_OK);
        }
        catch (\Exception $e){
            Log::error($e->getMessage());
            return $this->returnErrorResponse($e->getMessage() ?: __('general_error') , ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }

    }
}

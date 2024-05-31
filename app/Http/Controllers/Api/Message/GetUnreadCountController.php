<?php

namespace App\Http\Controllers\Api\Message;

use App\Http\Controllers\Controller;
use App\Repositories\Message\MessageRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class GetUnreadCountController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request) : JsonResponse
    {
        try {
            $unread = MessageRepository::getUnreadMessagesCount();
            return response()->json(['unread' => $unread]);
        } catch (\Exception $e) {
            return response()->json(['message' => __('messages.general_error')], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

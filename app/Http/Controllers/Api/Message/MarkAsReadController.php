<?php

namespace App\Http\Controllers\Api\Message;

use App\Http\Controllers\Controller;
use App\Repositories\Message\MessageRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MarkAsReadController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke($messageId) : JsonResponse
    {
        try {
            MessageRepository::markAsRead($messageId);
            return response()->json(['message' => 'Message marked as read']);
        }
        catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred while processing your request.'], 500);
        }
    }
}

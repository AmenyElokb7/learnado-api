<?php

namespace App\Http\Controllers\Api\Message;

use App\Http\Controllers\Controller;
use App\Repositories\Message\MessageRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MarkAsReadController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke($senderId) : JsonResponse
    {
        try {
            MessageRepository::markAsRead($senderId);
            return response()->json(['message' => 'Message marked as read']);
        }
        catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['message' => 'An error occurred while processing your request.'], 500);
        }
    }
}

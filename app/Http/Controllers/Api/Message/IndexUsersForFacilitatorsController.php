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

class IndexUsersForFacilitatorsController extends Controller
{
    use SuccessResponse,ErrorResponse;
    public function __invoke(Request $request) : JsonResponse
    {
        try {
            $users = MessageRepository::indexUsersForFacilitatorChat();
            return $this->returnSuccessResponse(__('users_retrieved_successfully'), $users, ResponseAlias::HTTP_OK);
        }
        catch (\Exception $e) {
            Log::error('Error retrieving users for facilitator chat', [
                'error' => $e->getMessage()
            ]);
            return $this->returnErrorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

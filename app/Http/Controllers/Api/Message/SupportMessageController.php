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
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
/**
 * @OA\Post(
 *     path="/api/designer/support-message",
 *     summary="Submit a support message by an authenticated user",
 *     tags={"Designer"},
 *     security={{"bearerAuth": {}}},
 *     @OA\RequestBody(
 *         required=true,
 *         description="Data required to submit a support message",
 *         @OA\JsonContent(
 *             required={"subject", "message"},
 *             @OA\Property(
 *                 property="subject",
 *                 type="string",
 *                 example="Language issue"
 *             ),
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="I want to add a new language to the platform"
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Message sent successfully",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="message_sent"
 *             ),
 *             @OA\Property(
 *                 property="success",
 *                 type="boolean",
 *                 example=true
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Internal Server Error",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="message",
 *                 type="string"
 *             ),
 *             @OA\Property(
 *                 property="success",
 *                 type="boolean",
 *                 example=false
 *             )
 *         )
 *     )
 * )
 */

class SupportMessageController extends Controller
{
    protected $messageRepository;

    public function __construct(MessageRepository $messageRepository)
    {
        $this->messageRepository = $messageRepository;
    }
    use ErrorResponse, SuccessResponse;

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

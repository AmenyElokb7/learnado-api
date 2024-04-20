<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Repositories\User\UserRepository;
use App\Traits\ErrorResponse;
use App\Traits\SuccessResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * @OA\Post(
 *     path="/api/send-password-reset-mail",
 *     summary="Send a password reset email",
 *     tags={"Auth"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                 required={"email"},
 *                 @OA\Property(
 *                     property="email",
 *                     type="string",
 *                     format="email",
 *                     description="User's email address to which the password reset link will be sent.",
 *                     example="user@example.com"
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Password reset email sent successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Password reset email sent successfully.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Bad Request",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Unable to process request due to invalid input.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Internal Server Error",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="An error occurred while processing your request.")
 *         )
 *     )
 * )
 */
class SendPasswordResetMailController extends Controller
{
    /**
     * Handle the incoming request.
     */
    protected $userRepository;
    use SuccessResponse, ErrorResponse;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function __invoke(Request $request)
    {
        try {
            $email = $request->input('email');
            $this->userRepository->sendPasswordResetMail($email);
            return $this->returnSuccessResponse(__('password_reset_mail_sent'), null, ResponseAlias::HTTP_OK);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            $errorDetails = json_decode($e->getMessage(), true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($errorDetails)) {
                return response()->json(['errors' => $errorDetails], $e->getCode() ?: ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
            } else {
                return $this->returnErrorResponse( $e->getMessage() ?: __('general_error'), ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
    }
}

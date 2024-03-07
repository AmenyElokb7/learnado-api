<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Repositories\User\UserRepository;
use App\Traits\ErrorResponse;
use App\Traits\SuccessResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

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
            return $this->returnErrorResponse($e->getMessage() ?: __('general_error'), $e->getCode() ?: ResponseAlias::HTTP_BAD_REQUEST);
        }
    }
}

<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\SetPasswordRequest;
use App\Repositories\User\UserRepository;
use App\Traits\ErrorResponse;
use App\Traits\SuccessResponse;
use Exception;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class ResetPasswordController extends Controller
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

    public function __invoke(SetPasswordRequest $request)
    {
        $token = $request->query('token');
        $newPassword = $request->input('password');
        try {

            $this->userRepository->updateUserPassword($token, $newPassword);
            return $this->returnSuccessResponse(__('password_reset'), null, ResponseAlias::HTTP_OK);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return $this->returnErrorResponse($e->getMessage() ?: __('general_error'), ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

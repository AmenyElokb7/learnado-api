<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Repositories\User\UserRepository;
use App\Traits\ErrorResponse;
use App\Traits\SuccessResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class UserProfileController extends Controller
{
    use SuccessResponse, ErrorResponse;

    protected $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        try{
            $user = $this->userRepository->getAuthenticatedUser();
            return $this->returnSuccessResponse(__('user_profile_retrieved'), $user, ResponseAlias::HTTP_OK);
        } catch (\Exception $exception) {
            return $this->returnErrorResponse($exception->getMessage(), $exception->getCode() ?: ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

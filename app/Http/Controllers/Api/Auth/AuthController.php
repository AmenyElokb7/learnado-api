<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuthenticateUserRequest;
use App\Repositories\Auth\AuthRepository;
use App\Traits\ErrorResponse;
use App\Traits\SuccessResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class AuthController extends Controller
{

    use ErrorResponse, SuccessResponse;

    protected $authRepository;

    public function __construct(AuthRepository $authRepository)
    {
        $this->authRepository = $authRepository;
    }

    /**
     * @param AuthenticateUserRequest $request
     * @return JsonResponse
     */
    public function __invoke(AuthenticateUserRequest $request): JsonResponse
    {
        $credentials = $request->validated();
        try {
            $result = $this->authRepository->authenticate($credentials);
            return $this->returnSuccessResponse('Login Successful', $result, ResponseAlias::HTTP_OK);
        } catch (Exception $exception) {
            return $this->returnErrorResponse($exception->getMessage(), ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

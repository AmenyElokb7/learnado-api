<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\SetPasswordRequest;
use App\Repositories\User\UserRepository;
use App\Traits\ErrorResponse;
use App\Traits\SuccessResponse;
use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;

/**
 * @OA\Post(
 *     path="/users/password/set",
 *     summary="Set a user password",
 *     tags={"User"},
 *     @OA\Response(response=200, description="Password set successfully"),
 *     @OA\Response(response=401, description="Invalid token"),
 * ),
 */
class SetPasswordController extends Controller
{

    use SuccessResponse, ErrorResponse;

    protected $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function __invoke(SetPasswordRequest $request): JsonResponse
    {
        return $this->userRepository->setPassword($request->token, $request->email, $request->password);

    }
}

<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Repositories\User\UserRepository;
use App\Traits\ErrorResponse;
use App\Traits\SuccessResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * @OA\Get(
 *     path="/user",
 *     summary="Get a user",
 *     tags={"User"},
 *     @OA\Parameter(
 *     name="email",
 *     in="query",),
 *     @OA\Response(
 *     response=200,
 *     description="User found"),
 * @OA\Response(
 *     response=404,
 *     description="User not found",
 *     @OA\JsonContent(
 *     @OA\Property(property="message", type="string", example="User not found")
 * )
 * )
 *     ),
 * */
class GetUserByEmailController extends Controller
{
    protected $userRepository;
    use ErrorResponse, SuccessResponse;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function __invoke(Request $request): JsonResponse
    {
        $user = $this->userRepository->findByEmail($request->email);
        try {
            if ($user) {
                return $this->returnSuccessResponse('User found', $user, ResponseAlias::HTTP_OK);
            } else {
                return $this->returnErrorResponse('User not found', ResponseAlias::HTTP_NOT_FOUND);
            }
        } catch (Exception $exception) {
            return $this->returnErrorResponse($exception->getMessage(), ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Repositories\User\UserRepository;
use App\Traits\ErrorResponse;
use App\Traits\SuccessResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * @OA\Get(
 *     path="/api/user/{email}",
 *     summary="Get a user",
 *     tags={"User"},
 *     @OA\Parameter(
 *     name="email",
 *     in="path",
 *     required=true,
 *     description="The email of the user to retrieve",
 *     @OA\Schema(type="string")
 *    ),
 *     @OA\Response(response=200, description="Successful operation",
 *     @OA\JsonContent(
 *              type="object",
 *              @OA\Property(property="message", type="string", example="User found"),
 *              @OA\Property(
 *                  property="user",
 *             type="object",
 *
 *              )
 *          )
 *     ),
 *     @OA\Response(response=400, description="Invalid request")
 * )
 */
class GetUserByIdController extends Controller
{
    protected $userRepository;
    use ErrorResponse, SuccessResponse;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function __invoke(Request $request): JsonResponse
    {
        try {
            $user = $this->userRepository->findById($request->id);
            return $this->returnSuccessResponse(__('user_detail'), $user, ResponseAlias::HTTP_OK);
        } catch (Exception $exception) {

            Log::error($exception->getMessage());
            return $this->returnErrorResponse(__('general_error'), ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

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
 *     path="/api/admin/user/{id}",
 *     summary="Get a user by ID",
 *     tags={"User"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="The ID of the user to retrieve",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="User found"),
 *             @OA\Property(
 *                 property="user",
 *                 type="object",
 *                 description="The retrieved user's details",
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="name", type="string", example="John Doe"),
 *                 @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
 *
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Invalid request",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Invalid ID supplied")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="User not found",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="No user found with the provided ID")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Internal Server Error",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="An error occurred while processing your request.")
 *         )
 *     )
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
            return $this->returnErrorResponse(__('user_not_found'), ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

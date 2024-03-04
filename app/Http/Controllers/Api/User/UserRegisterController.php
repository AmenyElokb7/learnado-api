<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegistrationRequest;
use App\Repositories\Auth\AuthRepository;
use App\Traits\ErrorResponse;
use App\Traits\SuccessResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * @OA\Post(
 *     path="/register/user",
 *      summary="Register a user",
 *     tags={"User"},
 *     @OA\Response(response=200,description="User registered successfully"),
 *      @OA\Response( response=400,description="An error occurred"),
 * )
 */
class UserRegisterController extends Controller
{
    use ErrorResponse, SuccessResponse;

    protected $authService;

    public function __construct(AuthRepository $authService)
    {
        $this->authService = $authService;
    }

    public function __invoke(RegistrationRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        try {
            $user = $this->authService->register($validatedData, 'user');
            return $this->returnSuccessResponse('User registered successfully', ResponseAlias::HTTP_OK, ['user' => $user]);
        } catch (ValidationException $exception) {
            return $this->returnErrorResponse($exception->validator->errors()->first(), ResponseAlias::HTTP_BAD_REQUEST);
        } catch (Exception) {
            return $this->returnErrorResponse('An error occurred', ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateUserAccountRequest;
use App\Repositories\Admin\AdminRepository;
use OpenApi\Annotations as OA;

/**
 * @OA\Post(
 *     path="/admin/create-user",
 *     summary="Create a new user account",
 *     tags={"Admin"},
 *     @OA\RequestBody(
 *     required=true,
 *     @OA\JsonContent(
 *     type="object",
 *     @OA\Property(property="first_name", type="string", example="John"),
 *     @OA\Property(property="last_name", type="string", example="Doe"),
 *     @OA\Property(property="email", type="string", example="JohnDoe@example.com"),
 *     @OA\Property(property="password", type="string", example="12345678"),
 *
 *     )
 * ),
 *     @OA\Response(response=200, description="Successful operation"),
 *     @OA\Response(response=400, description="Invalid request")
 * )
 */
class CreateUserAccountController extends Controller
{
    protected $adminRepository;

    /**
     * Handle the incoming request.
     */
    public function __construct(AdminRepository $adminRepository)
    {
        $this->adminRepository = $adminRepository;
    }

    public function __invoke(CreateUserAccountRequest $request)
    {
        $user = $this->adminRepository->createUserAccount($request->validated());
        return response()->json(['message' => 'User created successfully. Email sent for setting password.', 'user' => $user]);
    }
}

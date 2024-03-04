<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\Admin\AdminRepository;
use App\Traits\ErrorResponse;
use App\Traits\SuccessResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * @OA\Get(
 *     path="/admin/{email}",
 *     summary="Get an admin user",
 *     tags={"Admin"},
 *     @OA\Response(response=200, description="Successful operation"),
 *     @OA\Response(response=400, description="Invalid request")
 * )
 */
class GetAdminByEmailController extends Controller
{
    protected $adminRepository;
    use ErrorResponse, SuccessResponse;

    public function __construct(AdminRepository $adminRepository)
    {
        $this->adminRepository = $adminRepository;
    }

    public function __invoke(Request $request): JsonResponse
    {
        $admin = $this->adminRepository->findByEmail($request->email);
        if ($admin) {
            return $this->returnSuccessResponse('Admin found', ResponseAlias::HTTP_OK, $admin);
        } else {
            return $this->returnErrorResponse('Admin not found', ResponseAlias::HTTP_NOT_FOUND);
        }
    }
}

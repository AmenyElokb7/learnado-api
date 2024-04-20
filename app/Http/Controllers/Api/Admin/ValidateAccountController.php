<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\Admin\AdminRepository;
use App\Traits\ErrorResponse;
use App\Traits\SuccessResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * @OA\Post(
 *     path="/api/admin/validate-user-account/{id}",
 *     summary="Validate a user account",
 *     tags={"Admin"},
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="The ID of the user account to validate",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="User account validated successfully",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="User account validated successfully."
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Bad Request - Invalid ID supplied",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="Invalid ID supplied."
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Not Found - User account not found",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="User account not found."
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Internal Server Error - Unable to validate user account due to server error",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="An internal server error has occurred."
 *             )
 *         )
 *     )
 * )
 */

class ValidateAccountController extends Controller
{
    protected $adminRepository;
    use SuccessResponse, ErrorResponse;

    public function __construct(AdminRepository $adminRepository)
    {
        $this->adminRepository = $adminRepository;
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */

    public function __invoke(Request $request, $id): JsonResponse
    {

        try {
            $this->adminRepository->validateUserAccount($id);
            return $this->returnSuccessResponse(__('user_validated'), null, ResponseAlias::HTTP_OK);

        } catch (Exception $exception) {

            Log::error($exception->getMessage());
            return $this->returnErrorResponse($exception->getMessage(), ResponseAlias::HTTP_BAD_REQUEST);
        }

    }
}

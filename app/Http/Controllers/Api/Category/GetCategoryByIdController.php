<?php

namespace App\Http\Controllers\Api\Category;

use App\Http\Controllers\Controller;
use App\Repositories\Category\CategoryRepository;
use App\Traits\ErrorResponse;
use App\Traits\SuccessResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * @OA\Get(
 *     path="/api/categories/{id}",
 *     summary="Retrieve category by ID",
 *     tags={"Category"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *             example=1
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Category retrieved successfully",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Category retrieved successfully"),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="name", type="string", example="Electronics"),
 *                 @OA\Property(property="description", type="string", example="Devices and gadgets"),
 *                 @OA\Property(property="created_at", type="string", format="date-time", example="2024-05-21T12:00:00Z"),
 *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2024-05-21T12:00:00Z")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Invalid request due to incorrect input or missing fields"
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthorized - User not authorized to perform this action"
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Internal Server Error - Failed to retrieve category"
 *     )
 * )
 */

class GetCategoryByIdController extends Controller
{

    use SuccessResponse, ErrorResponse;
    protected $categoryRepository;
    /**
     * Handle the incoming request.
     */
    public function __construct(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    public function __invoke($id) : JsonResponse
    {
        try{
            $category = $this->categoryRepository->getCategory($id);
            return $this->returnSuccessResponse('Category retrieved successfully', $category, ResponseAlias::HTTP_OK);
        }catch(\Exception $e){
            return $this->returnErrorResponse($e->getMessage()?: __('general_error'), $e->getCode() ?: ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

<?php

namespace App\Http\Controllers\Api\Category;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateCategoryRequest;
use App\Repositories\Category\CategoryRepository;
use App\Traits\ErrorResponse;
use App\Traits\SuccessResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * @OA\Post(
 *     path="/api/admin/update-category/{id}",
 *     summary="Update a category",
 *     tags={"Admin"},
 *     security={
 *         {"bearerAuth": {}}
 *     },
 *     @OA\Parameter(
 *         name="category_id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *             example=1
 *         )
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(
 *                 required={"category"},
 *                 @OA\Property(property="category", type="string", example="Technology"),
 *                 @OA\Property(
 *                     property="media",
 *                     type="string",
 *                     format="binary",
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Category updated successfully",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Category updated successfully"),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="name", type="string", example="Technology"),
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
 *         description="Internal Server Error - Failed to update category"
 *     )
 * )
 */


class UpdateCategoryController extends Controller
{

    use SuccessResponse, ErrorResponse;

    /**
     * Handle the incoming request.
     * @param UpdateCategoryRequest $request
     * @param $category_id
     */

    protected $categoryRepository;

    public function __construct(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }
    public function __invoke(UpdateCategoryRequest $request, $category_id): JsonResponse
    {
        $category= $request->validated();
        try {
            $category = $this->categoryRepository->updateCategory($category, $category_id);
            return $this->returnSuccessResponse(__('category_updated_successfully'), $category, ResponseAlias::HTTP_OK);
        } catch (\Exception $e) {
            return $this->returnErrorResponse($e->getMessage()?: __('general_error'), $e->getCode()?: ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}

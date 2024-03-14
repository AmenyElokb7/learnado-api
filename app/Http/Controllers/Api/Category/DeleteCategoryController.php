<?php

namespace App\Http\Controllers\Api\Category;

use App\Http\Controllers\Controller;
use App\Repositories\Category\CategoryRepository;
use App\Traits\ErrorResponse;
use App\Traits\SuccessResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * @OA\Delete(
 *     path="/api/admin//delete-category/{id}",
 *     summary="Delete a category",
 *     tags={"Admin"},
 *     @OA\Parameter(
 *         name="categoryId",
 *         in="path",
 *         required=true,
 *         description="The unique identifier of the category to be deleted",
 *         @OA\Schema(
 *             type="integer"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Category deleted successfully",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="Category deleted successfully."
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Invalid request due to incorrect input or missing category ID"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Category not found"
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Internal Server Error - Failed to delete category"
 *     )
 * )
 */
class DeleteCategoryController extends Controller
{
    use SuccessResponse, ErrorResponse;

    protected $categoryRepository;

    public function __construct(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * @param $categoryId
     * @return JsonResponse
     */
    public function __invoke($categoryId): JsonResponse
    {
        try {
            $this->categoryRepository->deleteCategory($categoryId);
            return $this->returnSuccessResponse(__('category_deleted'), null, ResponseAlias::HTTP_OK);

        } catch (Exception $e) {
            Log::error($e->getMessage());
            return $this->returnErrorResponse($e->getMessage(), ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

<?php

namespace App\Http\Controllers\Api\Category;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateCategoryRequest;
use App\Repositories\Category\CategoryRepository;
use App\Traits\ErrorResponse;
use App\Traits\SuccessResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * @OA\Post(
 *     path="/api/admin/categories/create",
 *     summary="Create a new category",
 *     tags={"Admin"},
 *     @OA\RequestBody(
 *         required=true,
 *         description="Data needed to create a new category",
 *         @OA\JsonContent(
 *             required={"category"},
 *             @OA\Property(
 *                 property="category",
 *                 type="string",
 *                 description="The name of the category to be created. Must be unique and cannot exceed the maximum string length defined in the configuration.",
 *                 example="Books"
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Category created successfully",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="Category created successfully."
 *             ),
 *             @OA\Property(
 *                 property="category",
 *                 type="object",
 *                 @OA\Property(
 *                     property="id",
 *                     type="integer",
 *                     example=1
 *                 ),
 *                 @OA\Property(
 *                     property="category",
 *                     type="string",
 *                     example="Books"
 *                 ),
 *                 @OA\Property(
 *                     property="created_at",
 *                     type="string",
 *                     format="date-time",
 *                     example="2024-03-14T12:34:56.789Z"
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Invalid request due to incorrect input or missing fields"
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Internal Server Error - Failed to create category"
 *     )
 * )
 */
class CreateCategoryController extends Controller
{

    use SuccessResponse, ErrorResponse;

    protected $categoryRepository;

    public function __construct(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * @param CreateCategoryRequest $request
     * @return JsonResponse
     */
    public function __invoke(CreateCategoryRequest $request): JsonResponse
    {
        $data = $request->validated();

        try {
            $category = $this->categoryRepository->createCategory($data);
            return $this->returnSuccessResponse(__('category_created'), $category, ResponseAlias::HTTP_CREATED);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return $this->returnErrorResponse($e->getMessage() ?: __('general_error'), $e->getCode() ?: ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

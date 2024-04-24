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
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class UpdateCategoryController extends Controller
{

    use SuccessResponse, ErrorResponse;
    /**
     * Handle the incoming request.
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

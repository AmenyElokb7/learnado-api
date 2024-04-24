<?php

namespace App\Http\Controllers\Api\Category;

use App\Http\Controllers\Controller;
use App\Repositories\Category\CategoryRepository;
use App\Traits\ErrorResponse;
use App\Traits\SuccessResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

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

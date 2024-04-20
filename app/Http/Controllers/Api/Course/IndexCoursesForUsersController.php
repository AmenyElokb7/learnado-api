<?php

namespace App\Http\Controllers\Api\Course;

use App\Helpers\QueryConfig;
use App\Http\Controllers\Controller;
use App\Repositories\Course\CourseRepository;
use App\Traits\ErrorResponse;
use App\Traits\PaginationParams;
use App\Traits\SuccessResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class IndexCoursesForUsersController extends Controller
{
    use SuccessResponse, ErrorResponse, PaginationParams;

    public function __invoke(Request $request): JsonResponse
    {
        $paginationParams = $this->getAttributes($request);
        try {
            $courses = CourseRepository::index($paginationParams);
            return $this->returnSuccessPaginationResponse(__('course_found'), $courses, ResponseAlias::HTTP_OK, $paginationParams->isPaginated()
            );
        } catch (Exception $exception) {
            Log::error($exception->getMessage());
            return $this->returnErrorResponse($exception->getMessage() ?: __('general_error'), ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function getAttributes(Request $request): QueryConfig
    {
        $paginationParams = $this->getPaginationParams($request);

        $filters = [
            'is_public' => true,
            'is_active' => true,
            'keyword' => $paginationParams['KEYWORD'] ?? '',
            'category' => $request->input('category', null),
            'is_paid' => $request->input('price', null),
            'teaching_type' => $request->input('teaching_type', null),
        ];
        $order_by = [
            'created_at',
            'title',
            'final_price',

        ];
        $orderByField = in_array($paginationParams['ORDER_BY'], $order_by) ? $paginationParams['ORDER_BY'] : 'created_at';


        $search = new QueryConfig();
        $search->setFilters($filters)
            ->setPage($paginationParams['PAGE'])
            ->setPerPage($paginationParams['PER_PAGE'])
            ->setOrderBy($orderByField)
            ->setDirection($paginationParams['DIRECTION'])
            ->setPaginated($paginationParams['PAGINATION']);
        return $search;

    }
}

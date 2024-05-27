<?php

namespace App\Http\Controllers\Api\Course;

use App\Helpers\QueryConfig;
use App\Http\Controllers\Controller;
use App\Repositories\Course\CourseRepository;
use App\Traits\ErrorResponse;
use App\Traits\PaginationParams;
use App\Traits\SuccessResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class GetUpcomingCoursesController extends Controller
{
    use SuccessResponse,ErrorResponse,PaginationParams;
    public function __invoke(Request $request) : JsonResponse
    {
        $paginationParams = $this->getAttributes($request);
        try {
            $courses = CourseRepository::getUpcomingCourses($paginationParams);
            return $this->returnSuccessPaginationResponse(__('messages.upcoming_courses'), $courses, ResponseAlias::HTTP_OK, $paginationParams->isPaginated());
        }
        catch (\Exception $e) {
            Log::error($e->getMessage());
            return $this->returnErrorResponse(__('messages.error_processing_request'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    private function getAttributes(Request $request): QueryConfig
    {
        $paginationParams = $this->getPaginationParams($request);

        $search = new QueryConfig();
        $filters = [
            'is_public' => true,
            'is_active' => true,
            'is_offline' => false,
        ];
        $search->setFilters($filters)
            ->setPage($paginationParams['PAGE'])
            ->setPerPage($paginationParams['PER_PAGE'])
            ->setOrderBy($paginationParams['ORDER_BY'])
            ->setDirection($paginationParams['DIRECTION'])
            ->setPaginated($paginationParams['PAGINATION']);
        return $search;
    }
}

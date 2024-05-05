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

class IndexCourseCertificatesController extends Controller
{
    /**
     * Handle the incoming request.
     */
    use SuccessResponse, ErrorResponse, PaginationParams;
    public function __invoke(Request $request): JsonResponse
    {
        $paginationParams = $this->getAttributes($request);
        try {
            $courseCertificates = CourseRepository::indexCourseCertificates($paginationParams);
            return $this->returnSuccessPaginationResponse(__('course_certificates_found'), $courseCertificates, 200, $paginationParams->isPaginated());
        } catch (\Exception $exception) {
            return $this->returnErrorResponse($exception->getMessage() ?: __('general_error'), 500);
        }
    }
    private function getAttributes(Request $request): QueryConfig
    {
        $paginationParams = $this->getPaginationParams($request);

        $filters = [
            'keyword' => $paginationParams['KEYWORD'] ?? '',
        ];
        $search = new QueryConfig();
        $search->setFilters($filters)
            ->setPerPage($paginationParams['PER_PAGE'])
            ->setOrderBy($paginationParams['ORDER_BY'])
            ->setDirection($paginationParams['DIRECTION'])
            ->setPaginated($paginationParams['PAGINATION'])
            ->setPage($paginationParams['PAGE']);
        return $search;

    }
}

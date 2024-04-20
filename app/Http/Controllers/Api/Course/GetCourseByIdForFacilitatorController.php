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
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class GetCourseByIdForFacilitatorController extends Controller
{
    protected $courseRepository;
    use ErrorResponse, SuccessResponse, PaginationParams;

    public function __construct(CourseRepository $courseRepository)
    {
        $this->courseRepository = $courseRepository;
    }

    /**
     * @param $id
     * @return JsonResponse
     * @throws Exception
     */
    public function __invoke($id): JsonResponse
    {
        $course = $this->getAttributes();
        try {
            $course = $this->courseRepository->getCourseById($id, $course);
            return $this->returnSuccessResponse(__('course_found'), $course, ResponseAlias::HTTP_OK);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return $this->returnErrorResponse(__('course_not_found'), ResponseAlias::HTTP_NOT_FOUND);
        }
    }

    private function getAttributes(): QueryConfig
    {
        $filters = [
            'facilitator_id' => auth()->id(),
        ];
        $search = new QueryConfig();
        $search->setFilters($filters);
        return $search;

    }
}

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
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class GetCourseByIdForGuestController extends Controller
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
     */
    public function __invoke($id): JsonResponse
    {
        $course = $this->getAttributes();

        $course = $this->courseRepository->getCourseById($id, $course);
        return $this->returnSuccessResponse(__('course_found'), $course, ResponseAlias::HTTP_OK);

    }

    private function getAttributes(): QueryConfig
    {
        $filters = [
            'is_active' => true,
            'is_public' => true,
        ];
        $search = new QueryConfig();
        $search->setFilters($filters);
        return $search;

    }
}

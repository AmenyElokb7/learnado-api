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
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class GetCourseByIdForGuestController extends Controller
{
    use ErrorResponse, SuccessResponse, PaginationParams;

    /**
     * @param $id
     * @return JsonResponse
     */
    public function __invoke($id): JsonResponse
    {
        try{
            $course = $this->getAttributes();
            $course = CourseRepository::getCourseById($id, $course);
            return $this->returnSuccessResponse(__('course_found'), $course, ResponseAlias::HTTP_OK);
        }catch (\Exception $e){
            Log::error($e->getMessage());
            return $this->returnErrorResponse(__('course_not_found'), ResponseAlias::HTTP_NOT_FOUND);
        }
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

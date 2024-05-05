<?php

namespace App\Http\Controllers\Api\Course;

use App\Http\Controllers\Controller;
use App\Repositories\Course\CourseRepository;
use App\Traits\ErrorResponse;
use App\Traits\SuccessResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class CompleteCourseController extends Controller
{
    /**
     * Handle the incoming request.
     */
    use SuccessResponse, ErrorResponse;
    public function __invoke($course_id): JsonResponse
    {
        try{
            CourseRepository::completeCourse($course_id);
            return $this->returnSuccessResponse(__('course_completed'), null, ResponseAlias::HTTP_OK);
        }catch(\Exception $e){
            return $this->returnErrorResponse(__('general_error'), ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

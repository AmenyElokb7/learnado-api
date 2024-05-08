<?php

namespace App\Http\Controllers\Api\Course;

use App\Http\Controllers\Controller;
use App\Repositories\Course\CourseRepository;
use App\Traits\ErrorResponse;
use App\Traits\SuccessResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class SetCourseActiveController extends Controller
{
    /**
     * Handle the incoming request.
     * @param $course_id
     */
    use SuccessResponse, ErrorResponse;
    public function __invoke($course_id) : JsonResponse
    {
        try{
            CourseRepository::setCourseActive($course_id);
            return $this->returnSuccessResponse(__('course_active'), [], ResponseAlias::HTTP_OK);
        }
        catch (\Exception $exception) {
            Log::error($exception->getMessage());
            return $this->returnErrorResponse($exception->getMessage() ?: __('general_error'), ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

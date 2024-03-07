<?php

namespace App\Http\Controllers\Api\Course;

use App\Exceptions\Course\CourseException;
use App\Http\Controllers\Controller;
use App\Repositories\Course\CourseRepository;
use App\Traits\ErrorResponse;
use App\Traits\SuccessResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class UpdateCourseController extends Controller
{
    protected $courseRepository;
    use SuccessResponse, ErrorResponse;

    public function __construct(CourseRepository $courseRepository)
    {
        $this->courseRepository = $courseRepository;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */

    public function __invoke(Request $request)
    {
        $course_id = $request->course_id;
        $data = $request->all();
        try {
            $course = $this->courseRepository->updateCourseWithMedia($course_id, $data);
            return $this->returnSuccessResponse(__('course_updated'), $course, ResponseAlias::HTTP_OK);

        } catch (CourseException $e) {

            Log::error($e->getMessage());
            return $this->returnErrorResponse($e->getMessage(), ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        } catch (Exception $e) {

            Log::error($e->getMessage());
            return $this->returnErrorResponse(__('general_error'), ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

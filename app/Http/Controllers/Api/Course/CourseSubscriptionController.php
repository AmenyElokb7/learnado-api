<?php

namespace App\Http\Controllers\Api\Course;

use App\Http\Controllers\Controller;
use App\Repositories\Course\CourseRepository;
use App\Traits\ErrorResponse;
use App\Traits\SuccessResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class CourseSubscriptionController extends Controller
{
    use ErrorResponse, SuccessResponse;

    protected $courseRepository;

    public function __construct(CourseRepository $courseRepository)
    {
        $this->courseRepository = $courseRepository;
    }

    public function __invoke($courseId): JsonResponse
    {
        // users subscribe to a course
        $userId = auth()->user()->id;
        try {
            $course = $this->courseRepository->subscribeCourseToUsers($courseId, [$userId], false);
            return $this->returnSuccessResponse(__('course_subscribed'), $course, ResponseAlias::HTTP_OK);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return $this->returnErrorResponse($e->getMessage() ?: __('general_error'), $e->getCode() ?: ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

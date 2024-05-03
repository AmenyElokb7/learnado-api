<?php

namespace App\Http\Middleware;

use App\Models\Quiz;
use App\Traits\ErrorResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SubscribedMiddleware
{
    use ErrorResponse;

    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $quizId = $request->route('quiz_id');
        $quiz = Quiz::with('step.course')->find($quizId);

        if (!$quiz) {
            return $this->returnErrorResponse(__('quiz_not_found'), Response::HTTP_NOT_FOUND);
        }

        $course = $quiz->step->course;

        if (!$course) {
            return $this->returnErrorResponse(__('course_not_found'), Response::HTTP_NOT_FOUND);
        }

        if (!$request->user()->subscribedCourses()->where('course_id', $course->id)->exists()) {
            return $this->returnErrorResponse(__('not_subscribed'), Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}

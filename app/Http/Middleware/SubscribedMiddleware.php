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
        // whether course or learning path
        $quiz = Quiz::find($quizId);

        if (!$quiz) {
            return $this->returnErrorResponse(__('quiz_not_found'), Response::HTTP_NOT_FOUND);
        }

        $course = $quiz->step->course ?? null;
        $learningPath = $quiz->learningPath ?? null;
        if (!$course && !$learningPath) {
            return $this->returnErrorResponse(__('quiz_not_found'), Response::HTTP_NOT_FOUND);
        }

        $user = auth()->user();
        if ($course) {
            $isSubscribed = $user->subscribedCourses->contains($course->id);
        } else {
            $isSubscribed = $user->subscribedLearningPaths->contains($learningPath->id);
        }
        if (!$isSubscribed) {
            return $this->returnErrorResponse(__('not_subscribed'), Response::HTTP_FORBIDDEN);
        }
        return $next($request);
    }
}

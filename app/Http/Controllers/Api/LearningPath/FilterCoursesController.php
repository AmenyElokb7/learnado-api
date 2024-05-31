<?php

namespace App\Http\Controllers\Api\LearningPath;

use App\Http\Controllers\Controller;
use App\Repositories\LearningPath\LearningPathRepository;
use App\Traits\ErrorResponse;
use App\Traits\SuccessResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class FilterCoursesController extends Controller
{
    /**
     * Handle the incoming request.
     */
    protected $learningPathRepository;

    public function __construct(LearningPathRepository $learningPathRepository)
    {
        $this->learningPathRepository = $learningPathRepository;
    }
    use SuccessResponse,ErrorResponse;
    public function __invoke(Request $request) : JsonResponse
    {
        try{
            $params = [
                'category_id' => $request->query('category_id'),
                'language_id' => $request->query('language_id'),
                'is_public'   => $request->query('is_public')
            ];

            $courses= $this->learningPathRepository->filterCourses($params);
            return $this->returnSuccessResponse('Courses filtered successfully',$courses, ResponseAlias::HTTP_OK);
        }catch(\Exception $exception){
            Log::error($exception->getMessage());
            return $this->returnErrorResponse($exception->getMessage(), ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

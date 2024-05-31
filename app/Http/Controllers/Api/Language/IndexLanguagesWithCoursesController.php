<?php

namespace App\Http\Controllers\Api\Language;

use App\Helpers\QueryConfig;
use App\Http\Controllers\Controller;
use App\Repositories\Language\LanguageRepository;
use App\Traits\ErrorResponse;
use App\Traits\PaginationParams;
use App\Traits\SuccessResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class IndexLanguagesWithCoursesController extends Controller
{
    /**
     * Handle the incoming request.
     */
    use SuccessResponse, ErrorResponse,PaginationParams;
    public function __invoke(Request $request) : JsonResponse
    {
        $paginationParams = $this->getAttributes($request);
        try {
            $languages = LanguageRepository::indexLanguagesWithCourses($paginationParams);
            return $this->returnSuccessPaginationResponse(__('languages'), $languages, ResponseAlias::HTTP_OK, $paginationParams->getPerPage());
        } catch (\Exception $exception) {
            return $this->returnErrorResponse($exception->getMessage() ?: __('general_error'), ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    private function getAttributes(Request $request): QueryConfig
    {
        $paginationParams = $this->getPaginationParams($request);
        $search = new QueryConfig();

        $filters = [
            'language ' => $request->input('keyword'),
        ];
        $search->setFilters($filters)
            ->setPerPage($paginationParams['PER_PAGE'])
            ->setOrderBy($paginationParams['ORDER_BY'])
            ->setDirection($paginationParams['DIRECTION'])
            ->setPaginated($paginationParams['PAGINATION']);
        return $search;

    }
}

<?php

namespace App\Http\Controllers\Api\Language;

use App\Helpers\QueryConfig;
use App\Http\Controllers\Controller;
use App\Repositories\Language\LanguageRepository;
use App\Traits\ErrorResponse;
use App\Traits\PaginationParams;
use App\Traits\SuccessResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * @OA\Get(
 *     path="/api/admin/languages",
 *     summary="List languages with optional filtering and pagination",
 *     tags={"Language"},
 *     @OA\Parameter(
 *         name="language",
 *         in="query",
 *         description="Filter by language name",
 *         required=false,
 *         @OA\Schema(
 *             type="string"
 *         )
 *     ),
 *     @OA\Parameter(
 *         name="page",
 *         in="query",
 *         description="Page number for pagination",
 *         required=false,
 *         @OA\Schema(
 *             type="integer"
 *         )
 *     ),
 *     @OA\Parameter(
 *         name="perPage",
 *         in="query",
 *         description="Number of items to return per page for pagination",
 *         required=false,
 *         @OA\Schema(
 *             type="integer"
 *         )
 *     ),
 *     @OA\Parameter(
 *         name="orderBy",
 *         in="query",
 *         description="Field to order by",
 *         required=false,
 *         @OA\Schema(
 *             type="string"
 *         )
 *     ),
 *     @OA\Parameter(
 *         name="direction",
 *         in="query",
 *         description="Direction of sort, either asc or desc",
 *         required=false,
 *         @OA\Schema(
 *             type="string",
 *             enum={"asc", "desc"}
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="Languages fetched successfully."
 *             ),
 *
 *             @OA\Property(
 *                 property="pagination",
 *                 type="object",
 *                 @OA\Property(property="current_page", type="integer", example=1),
 *                 @OA\Property(property="per_page", type="integer", example=15),
 *                 @OA\Property(property="total_pages", type="integer", example=10),
 *                 @OA\Property(property="total_items", type="integer", example=150)
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Internal Server Error"
 *     )
 * )
 */
class IndexLanguagesController extends Controller
{
    use ErrorResponse, SuccessResponse, PaginationParams;

    protected $languageRepository;

    public function __construct(LanguageRepository $languageRepository,)
    {
        $this->languageRepository = $languageRepository;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
    {
        $paginationParams = $this->getAttributes($request);

        try {
            $languages = $this->languageRepository->indexLanguages($paginationParams);
            return $this->returnSuccessPaginationResponse(__('language_fetched'), $languages, ResponseAlias::HTTP_OK, $paginationParams->isPaginated());
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return $this->returnErrorResponse($e->getMessage(), ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param Request $request
     * @return QueryConfig
     */
    private function getAttributes(Request $request): QueryConfig
    {
        $paginationParams = $this->getPaginationParams($request);
        $filters = [
            'language' => $request->input('keyword')
        ];
        $search = new QueryConfig();
        $search->setFilters($filters)
            ->setPerPage($paginationParams['PER_PAGE'])
            ->setOrderBy($paginationParams['ORDER_BY'])
            ->setDirection($paginationParams['DIRECTION'])
            ->setPaginated($paginationParams['PAGINATION'])
            ->setPage($paginationParams['PAGE']);
        return $search;
    }
}

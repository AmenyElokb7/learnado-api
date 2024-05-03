<?php

namespace App\Http\Controllers\Api\Language;

use App\Http\Controllers\Controller;
use App\Repositories\Language\LanguageRepository;
use App\Traits\ErrorResponse;
use App\Traits\SuccessResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * @OA\Delete(
 *     path="/api/admin/delete-language/{id}",
 *     summary="Delete a language",
 *     tags={"Admin"},
 *     @OA\Parameter(
 *         name="languageId",
 *         in="path",
 *         required=true,
 *         description="The ID of the language to delete",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Language deleted successfully",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="Language deleted successfully."
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Invalid request due to incorrect input or missing language ID"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Language not found"
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Internal Server Error - Failed to delete language"
 *     )
 * )
 */
class DeleteLanguageController extends Controller
{

    use SuccessResponse, ErrorResponse;

    protected $languageRepository;

    public function __construct(LanguageRepository $languageRepository)
    {
        $this->languageRepository = $languageRepository;
    }

    /**
     * @param $languageId
     * @return JsonResponse
     */
    public function __invoke($languageId): JsonResponse
    {
        try {
            $this->languageRepository->deleteLanguage($languageId);
            return $this->returnSuccessResponse(__('language_deleted'), null, ResponseAlias::HTTP_OK);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return $this->returnErrorResponse($e->getMessage(), ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

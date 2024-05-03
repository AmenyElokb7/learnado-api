<?php

namespace App\Http\Controllers\Api\Language;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateLanguageRequest;
use App\Repositories\Language\LanguageRepository;
use App\Traits\ErrorResponse;
use App\Traits\SuccessResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * @OA\Post(
 *     path="/api/admin/create-language",
 *     summary="Create a new language",
 *     tags={"Admin"},
 *     @OA\RequestBody(
 *         required=true,
 *         description="Data needed to create a new language",
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                 type="object",
 *                 required={"language"},
 *                 @OA\Property(
 *                     property="language",
 *                     type="string",
 *                     description="The name of the language to be created. Must be unique and adhere to maximum length constraints.",
 *                     example="French"
 *                 ),
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Language created successfully",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="Language created successfully"
 *             ),
 *             @OA\Property(
 *                 property="language",
 *                 type="object",
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="language", type="string", example="French")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Invalid request data",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="Invalid request data"
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Internal Server Error",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="An error occurred while processing your request."
 *             )
 *         )
 *     )
 * )
 */
class CreateLanguageController extends Controller
{

    protected $languageRepository;
    use SuccessResponse, ErrorResponse;

    public function __construct(LanguageRepository $languageRepository)
    {
        $this->languageRepository = $languageRepository;
    }

    /**
     * @param CreateLanguageRequest $request
     * @return JsonResponse
     */

    public function __invoke(CreateLanguageRequest $request): JsonResponse
    {
        $data = $request->validated();
        try {
            $language = $this->languageRepository->createLanguage($data);
            return $this->returnSuccessResponse('language_created', $language, ResponseAlias::HTTP_CREATED);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return $this->returnErrorResponse($e->getMessage(), ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }

    }
}

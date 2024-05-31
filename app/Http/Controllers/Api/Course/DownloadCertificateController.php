<?php

namespace App\Http\Controllers\Api\Course;

use App\Http\Controllers\Controller;
use App\Repositories\Course\CourseRepository;
use App\Traits\ErrorResponse;
use App\Traits\SuccessResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
/**
 * @OA\Get(
 *     path="/api/certificates/download/{certificateId}",
 *     summary="Download course certificate",
 *     tags={"Course"},
 *     security={
 *         {"bearerAuth": {}}
 *     },
 *     @OA\Parameter(
 *         name="certificate_id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *             example=1
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Certificate downloaded successfully",
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Invalid request due to incorrect input or missing fields"
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthorized - User not authorized to perform this action"
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Internal Server Error - Failed to download certificate"
 *     )
 * )
 */

class DownloadCertificateController extends Controller
{
    use SuccessResponse, ErrorResponse;

    /**
     * Handle the incoming request.
     * @param $certificate_id
     * @return BinaryFileResponse|JsonResponse
     */
    public function __invoke($certificate_id): BinaryFileResponse|JsonResponse
    {
        try {
            $file_path = CourseRepository::getCertificateFilePath($certificate_id);
            return response()->download($file_path);
        } catch (\Exception $e) {
            return $this->returnErrorResponse($e->getMessage() ?: __('general_error'), $e->getCode() ?: ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

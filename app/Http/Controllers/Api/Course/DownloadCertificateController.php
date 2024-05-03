<?php

namespace App\Http\Controllers\Api\Course;

use App\Http\Controllers\Controller;
use App\Repositories\Course\CourseRepository;
use App\Traits\ErrorResponse;
use App\Traits\SuccessResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class DownloadCertificateController extends Controller
{
    use SuccessResponse, ErrorResponse;
    protected $courseRepository;

    public function __construct(CourseRepository $courseRepository)
    {
        $this->courseRepository = $courseRepository;
    }

    /**
     * Handle the request to download a certificate.
     *
     * @param int $certificateId
     * @return BinaryFileResponse|JsonResponse
     */
    public function __invoke($certificateId): BinaryFileResponse|JsonResponse
    {
        try {
            $filePath = $this->courseRepository->getCertificateFilePath($certificateId);
            return response()->download($filePath);
        } catch (\Exception $e) {
            return $this->returnErrorResponse($e->getMessage() ?: __('general_error'), $e->getCode() ?: ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

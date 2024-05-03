<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\Admin\AdminRepository;
use App\Traits\ErrorResponse;
use App\Traits\SuccessResponse;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;


class RejectUserAccountController extends Controller
{
    protected $adminRepository;

    use SuccessResponse, ErrorResponse;
    /**
     * Handle the incoming request.
     */

    function __construct(AdminRepository $adminRepository)
    {
        $this->adminRepository = $adminRepository;
    }
    /**
     * @param $id
     * @return JsonResponse
     */

    public function __invoke($id) : JsonResponse
    {
        try {
            $this->adminRepository->rejectUserAccount($id);
            return $this->returnSuccessResponse(__('user_account_rejected'), null, ResponseAlias::HTTP_OK);
        } catch (\Exception $exception) {
            return $this->returnErrorResponse($exception->getMessage(), $exception->getCode() ?:ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

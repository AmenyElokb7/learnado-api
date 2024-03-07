<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProfileRequest;
use App\Repositories\User\UserRepository;
use App\Traits\ErrorResponse;
use App\Traits\SuccessResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class UpdateProfileController extends Controller
{
    /**
     * Handle the incoming request.
     */
    use ErrorResponse, SuccessResponse;

    protected $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @param UpdateProfileRequest $request
     * @return JsonResponse
     */


    public function __invoke(UpdateProfileRequest $request)
    {
        $user = $this->getAttributes($request);
        try {
            $userProfileData = $this->userRepository->updateProfile($user);
            return $this->returnSuccessResponse(__('user_update'), $userProfileData, ResponseAlias::HTTP_OK);
        } catch (Exception $exception) {
            Log::error($exception->getMessage());
            return $this->returnErrorResponse($exception->getMessage() ?: __('general_error'), $exception->getCode() ?: ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param UpdateProfileRequest $request
     * @return array
     */

    private function getAttributes(UpdateProfileRequest $request): array
    {
        $validatedData = $request->validated();
        $modifiedData = [];

        foreach ($validatedData as $key => $value) {
            if ($request->has($key) && $request->input($key) !== $value) {
                $modifiedData[$key] = $value;
            }
        }
        return $modifiedData;
    }
}

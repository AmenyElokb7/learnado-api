<?php

namespace App\Repositories\Auth;

use App\Repositories\Admin\AdminRepository;
use App\Repositories\Media\MediaRepository;
use App\Repositories\User\UserRepository;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthRepository
{

    protected $userRepository;
    protected $adminRepository;
    protected $mediaRepository;

    public function __construct(UserRepository $userRepository, AdminRepository $adminRepository, MediaRepository $mediaRepository)
    {
        $this->userRepository = $userRepository;
        $this->adminRepository = $adminRepository;
        $this->mediaRepository = $mediaRepository;
    }

    /**
     * Register a new user account
     * @param array $data
     * @return Model
     */

    public final function register(array $data): Model
    {
        $data['password'] = bcrypt($data['password']);
        $user = $this->userRepository->create($data);
        $profilePicture = $data['profile_picture'] ?? null;
        if ($profilePicture instanceof UploadedFile) {

            MediaRepository::attachMediaToModel($user, $profilePicture);


        }
        return $user;
    }

    /**
     * Authenticate a user and generate access and refresh tokens
     * @param array $credentials
     * @return array
     * @throws Exception
     */

    public final function authenticate(array $credentials): array

    {
        $user = null;
        $token = null;

        if ($token = auth()->guard('user')->attempt($credentials)) {

            $user = auth()->guard('user')->user();
        } else {

            throw new Exception(__('user_authenticated_failed'), ResponseAlias::HTTP_UNAUTHORIZED);
        }
        if (!$user->is_valid) {

            throw new Exception(__('user_not_validated'), ResponseAlias::HTTP_UNAUTHORIZED);
        }
        $refreshToken = $this->generateRefreshToken($user);

        return [
            'access_token' => $token,
            'refresh_token' => $refreshToken,
            'refresh_token_expires_in' => call_user_func(config('constants.REFRESH_TOKEN_EXPIRATION_IN_DAYS')),
            'user' => $user
        ];
    }

    /**
     * Generate a refresh token
     * @param $user
     * @return string
     * @throws Exception
     */
    public final function generateRefreshToken($user): string
    {
        try {
            $customClaims = ['token_type' => 'refresh'];
            JWTAuth::factory()->setTTL(10080);
            return JWTAuth::claims($customClaims)->fromUser($user);
        } catch (Exception $exception) {
            Log::error($exception->getMessage());
            throw new Exception(__('messages.token_generation_failed'), ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

<?php

namespace App\Repositories\Auth;

use App\Repositories\Admin\AdminRepository;
use App\Repositories\Media\MediaRepository;
use App\Repositories\User\UserRepository;
use Exception;
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
     * @param string $accountType
     * @return array
     */

    public final function register(array $data, string $accountType): array
    {
        $data['password'] = bcrypt($data['password']);
        $profilePicture = $data['profile_picture'] ?? null;

        if ($accountType === 'user') {
            $user = $this->userRepository->create($data);
        } elseif ($accountType === 'admin') {
            $data['role'] = 'admin';
            $data['is_valid'] = true; // Admin accounts are validated by default
            $user = $this->adminRepository->create($data);
        } else {
            $data['role'] = $accountType;
            $data['is_valid'] = false; // User accounts are not validated by default
            $user = $this->adminRepository->create($data);
        }
        if ($profilePicture instanceof UploadedFile) {
            $media = MediaRepository::attachMediaToModel($user, $profilePicture, 'user');
            $fullUrl = $media->file_name;
        }
        return [
            'user_info' => $user,
            'profile_picture' => $fullUrl ?? null,
        ];
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
        if ($token = auth()->guard('User')->attempt($credentials)) {
            $user = auth()->guard('User')->user();
        } elseif ($token = auth()->guard('admin')->attempt($credentials)) {
            $user = auth()->guard('admin')->user();
        } else {
            throw new Exception('Invalid credentials', ResponseAlias::HTTP_UNAUTHORIZED);
        }
        if (!$user->is_valid) {
            throw new Exception('Account is not validated. Please contact support.', ResponseAlias::HTTP_UNAUTHORIZED);
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
     */
    public final function generateRefreshToken($user): string
    {
        $customClaims = ['token_type' => 'refresh'];
        JWTAuth::factory()->setTTL(10080);
        $refreshToken = JWTAuth::claims($customClaims)->fromUser($user);
        return $refreshToken;
    }
}

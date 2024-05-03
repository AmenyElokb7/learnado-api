<?php

namespace App\Repositories\Auth;

use App\Models\User;
use App\Repositories\Admin\AdminRepository;
use App\Repositories\Media\MediaRepository;
use App\Repositories\User\UserRepository;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

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
        return $this->userRepository->create($data);
    }

    /**
     * Authenticate a user and generate access and refresh tokens
     * @param array $credentials
     * @return array
     * @throws Exception
     */

    public final function authenticate(array $credentials): array
    {
        $email = $credentials['email'] ?? null;
        $password = $credentials['password'] ?? null;
        $user = User::where('email', $email)->first();
        if (!$user) {
            throw new Exception(json_encode(['email' => __('messages.user_not_found')]), ResponseAlias::HTTP_UNAUTHORIZED);
        }
        if (!Hash::check($password, $user->password)) {
            throw new Exception(json_encode(['password' => __('messages.password_incorrect')]), ResponseAlias::HTTP_UNAUTHORIZED);
        }
        if (!$user->is_valid) {
            throw new Exception(__('user_not_validated'), ResponseAlias::HTTP_FORBIDDEN);
        }

        $token = Auth::setTTL(config('jwt.ttl'))
            ->claims(['refresh_token' => false])
            ->login($user);

        $refreshToken = Auth::setTTL(config('jwt.refresh_ttl'))
            ->claims(['refresh_token' => true])
            ->login($user);

        return [
            'access_token' => $token,
            'refresh_token' => $refreshToken,
            'user' => $user,
            'media' => $user->media()->first(),
        ];
    }
    public final function refreshToken(): array
    {
        $user = auth()->user();
        $token = auth()->setTTL(config('jwt.ttl'))
            ->claims(['refresh_token' => false])
            ->login($user);
        $refreshToken = auth()->setTTL(config('jwt.refresh_ttl'))
            ->claims(['refresh_token' => true])
            ->login($user);
        return [
            'access_token' => $token,
            'refresh_token' => $refreshToken
        ];
    }


}

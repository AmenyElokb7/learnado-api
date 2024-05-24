<?php

namespace App\Repositories\User;

use App\Helpers\QueryConfig;
use App\Mail\ResetPasswordMail;
use App\Models\PasswordResetToken;
use App\Models\User;
use App\Repositories\Media\MediaRepository;
use App\Traits\ErrorResponse;
use App\Traits\PaginationParams;
use App\Traits\SuccessResponse;
use Exception;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class UserRepository
{
    use SuccessResponse, ErrorResponse, PaginationParams;

    /**
     * @param array $data
     * @return User
     */
    public final function create(array $data): User
    {
        return User::create($data);
    }

    /**
     * @param QueryConfig $queryConfig
     * @return LengthAwarePaginator|Collection
     */

    public static function index(QueryConfig $queryConfig): LengthAwarePaginator|Collection
    {
        $UserQuery = User::with('media')->withCount('courses')->newQuery();
        User::applyFilters($queryConfig->getFilters(), $UserQuery);
        $users = $UserQuery->orderBy($queryConfig->getOrderBy(), $queryConfig->getDirection())->get();
        if ($queryConfig->getPaginated()) {
            return self::applyPagination($users, $queryConfig);
        }
        return $users;
    }

    /**
     * @param int $UserId
     * @return User|Builder|Model
     * @throws Exception
     */
    public final function findById(int $UserId): User|Builder|Model
    {
        $user = User::with('media')->where('id', $UserId)->first();
        if(!$user){
            throw new Exception(__('user_not_found'), ResponseAlias::HTTP_NOT_FOUND);
        }
        return $user;
    }

    public final function getAuthenticatedUser(): User {
        return Auth::user()->load('media');
    }

    /**
     * @param $token
     * @param $password
     * @return JsonResponse
     * @throws Exception
     */
    public final function setPassword($token, $password): JsonResponse
    {
        $tokenData = PasswordResetToken::where('token', $token)->first();
        if (!$tokenData) {

            throw new Exception(__('token_invalid'), ResponseAlias::HTTP_BAD_REQUEST);
        }
        if ($tokenData->expires_at < now()->timestamp){
            PasswordResetToken::where('token', $tokenData->token)->delete();
            throw new Exception(__('token_expired'), ResponseAlias::HTTP_BAD_REQUEST);
        }
        $user = User::where('email', $tokenData->email)->first();
        if (!$user) {
            throw new Exception(__('user_not_found'), ResponseAlias::HTTP_NOT_FOUND);
        }
        $user->password = bcrypt($password);
        $user->save();
        PasswordResetToken::where('email', $user->email)->delete();
        return $this->returnSuccessResponse(__('password_reset'), null, ResponseAlias::HTTP_OK);
    }

    /**
     * update user account
     * @param array $data
     * @return User $user
     * @throws Exception
     */

    public final function updateProfile(array $data) : User
    {
        $user = Auth::user();
        if (!$user) {
            throw new Exception(__('user_authenticated_failed'), ResponseAlias::HTTP_NOT_FOUND);
        }
        $password = $data['password'] ?? null;
        if(isset($password)){
            $data['password'] = bcrypt($password);
        }
        $currentMedia = $user->media->first();
        $profilePicture = $data['profile_picture'] ?? null;
        $deletedMedia = $data['deleted_files_id'] ?? null;
        if ($deletedMedia)
            MediaRepository::detachMediaFromModel($user, $currentMedia->id);
         else if ($profilePicture)
             MediaRepository::attachOrUpdateMediaForModel($user, $profilePicture, $currentMedia->id ?? null);

        $user->fill($data);
        $user->save();
        return User::with('media')->where('id', $user->id)->first();
    }

    /**
     * Reset user password
     * @param $email
     * @throws Exception
     */

    public final function sendPasswordResetMail($email): void
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            throw new Exception(json_encode(['email' => __('messages.user_not_found')]), ResponseAlias::HTTP_UNAUTHORIZED);
        }
        self::createPasswordResetToken($email);
        $token = PasswordResetToken::where('email', $email)->first()->token;
        Mail::to($user->email)->send(new ResetPasswordMail($token, $user->email));
    }

    /**
     * @param $email
     * @throws Exception
     */
    private static function createPasswordResetToken($email): void
    {
        PasswordResetToken::create([
            'email' => $email,
            'token' => hash('sha256', Str::random(60)),
            'created_at' => now()->timestamp,
            'expires_at' => now()->addMinutes(15)->timestamp,
        ]);
    }
}


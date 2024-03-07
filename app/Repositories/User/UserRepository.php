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
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
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

    public static function index(QueryConfig $queryConfig): LengthAwarePaginator|Collection
    {
        $UserQuery = User::with('media')->newQuery();
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
        if ($user) {
            $profilePicture = $user->media->first() ? Storage::url($user->media->first()->file_name) : null;
            $user->profile_picture = $profilePicture;
        } else {
            throw new Exception(__('messages.user_not_found'), ResponseAlias::HTTP_NOT_FOUND);
        }
        return $user;
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
        if ($tokenData->expires_at < now()) {
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

    public final function updateProfile(array $data): User
    {
        $user = Auth::user();
        if (!$user) {
            throw new Exception(__('user_authenticated_failed'), ResponseAlias::HTTP_NOT_FOUND);
        }
        if ($user instanceof User) {
            $user->fill($data);
            $user->save();

            $profilePicture = $data['profile_picture'] ?? null;

            if ($user->media->first()) {
                if (isset($data['profile_picture'])) {
                    MediaRepository::updateMediaFromModel($user, $profilePicture, $user->media->first()->id ?? null);
                } else {
                    MediaRepository::detachMediaFromModel($user, $user->media->first()->id);
                }

            } else {
                if ($profilePicture instanceof UploadedFile) {
                    MediaRepository::attachMediaToModel($user, $profilePicture);
                }
            }


        }
        return $user;
    }


    /**
     * Reset user password
     *
     */

    public final function sendPasswordResetMail($email): void
    {
        $user = User::where('email', $email)->first();
        if (!$user) {
            throw new Exception(__('user_not_found'), ResponseAlias::HTTP_NOT_FOUND);
        }
        self::createPasswordResetToken($email);
        $token = PasswordResetToken::where('email', $email)->first()->token;
        Mail::to($user->email)->send(new ResetPasswordMail($token, $user->email));

    }

    private static function createPasswordResetToken($email): void
    {
        PasswordResetToken::create([
            'email' => $email,
            'token' => hash('sha256', Str::random(60)),
            'created_at' => now(),
            'expires_at' => now()->addMinutes(15),
        ]);
    }

    /**
     * @throws Exception
     */
    public final function updateUserPassword($token, $newPassword): void
    {
        $passwordResetToken = PasswordResetToken::where('token', $token)
            ->first();

        if ($passwordResetToken->expires_at < now()) {
            PasswordResetToken::where('token', $passwordResetToken->token)->delete();

            throw new Exception(__('token_expired'), ResponseAlias::HTTP_BAD_REQUEST);
        }
        $email = $passwordResetToken->email;

        $user = User::where('email', $email)->first();

        if (!$user) {
            throw new Exception(__('user_not_found'), ResponseAlias::HTTP_NOT_FOUND);
        }

        $user->password = bcrypt($newPassword);
        $user->save();

        PasswordResetToken::where('token', $passwordResetToken->token)->delete();

    }
}


<?php

namespace App\Repositories\User;

use App\Helpers\QueryConfig;
use App\Models\PasswordReset;
use App\Models\User;
use App\Traits\ErrorResponse;
use App\Traits\PaginationParams;
use App\Traits\SuccessResponse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class UserRepository
{
    use SuccessResponse, ErrorResponse, PaginationParams;

    /**
     * @param QueryConfig $queryConfig
     * @return LengthAwarePaginator|Collection
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
     * @param string $email
     * @return User|null
     */
    public final function findByEmail(string $email): User|Builder|Model
    {
        $user = User::with('media')->where('email', $email)->first();
        if ($user) {
            $profilePicture = $user->media->first() ? Storage::url($user->media->first()->file_name) : null;
            $user->profile_picture = $profilePicture;
        }
        return $user;
    }

    /**
     * @param $token
     * @param $email
     * @param $password
     * @return JsonResponse
     */
    public final function setPassword($token, $email, $password): JsonResponse
    {

        $tokenData = PasswordReset::where('token', $token)->first();
        if (!$tokenData || $tokenData->email != $email) {
            return $this->returnErrorResponse('Invalid token', ResponseAlias::HTTP_BAD_REQUEST);
        }
        if ($tokenData->expires_at < now()) {
            PasswordReset::where('token', $tokenData->token)->delete();
            return $this->returnErrorResponse('Token expired', ResponseAlias::HTTP_BAD_REQUEST);
        }
        $user = User::where('email', $email)->first();
        if (!$user) {
            return $this->returnErrorResponse('User not found', ResponseAlias::HTTP_NOT_FOUND);
        }
        $user->password = bcrypt($password);
        $user->save();
        PasswordReset::where('email', $user->email)->delete();
        return $this->returnSuccessResponse('Password reset successfully', null, ResponseAlias::HTTP_OK);
    }

}

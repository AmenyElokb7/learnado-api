<?php

namespace App\Repositories\Admin;

use App\Helpers\QueryConfig;
use App\Mail\AccountInvalidationMail;
use App\Mail\AccountValidated;
use App\Mail\SetPasswordMail;
use App\Models\Admin;
use App\Models\PasswordReset;
use App\Models\User;
use App\Repositories\Media\MediaRepository;
use App\Traits\ErrorResponse;
use App\Traits\PaginationParams;
use App\Traits\SuccessResponse;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;


class AdminRepository
{
    use SuccessResponse, ErrorResponse, PaginationParams;

    public final function create(array $data): Admin
    {
        return Admin::create($data);
    }

    public static function index(QueryConfig $queryConfig): LengthAwarePaginator|Collection
    {
        $UserQuery = Admin::with('media')->newQuery();
        Admin::applyFilters($queryConfig->getFilters(), $UserQuery);
        $admins = $UserQuery->orderBy($queryConfig->getOrderBy(), $queryConfig->getDirection())->get();
        if ($queryConfig->getPaginated()) {
            return self::applyPagination($admins, $queryConfig);
        }
        return $admins;
    }

    /**
     * @param string $email
     * @return Admin|Builder|Model
     */
    public final function findByEmail(string $email): Admin|Builder|Model
    {
        $admin = Admin::with('media')->where('email', $email)->first();
        if ($admin) {
            $profilePicture = $admin->media->first() ? Storage::url($admin->media->first()->file_name) : null;
            $admin->profile_picture = $profilePicture;
        }
        return $admin;
    }

    /**
     * @param string $email
     * @return User
     * @throws Exception
     */
    public final function validateUserAccount(string $email): User
    {
        $user = User::where('email', $email)->first();
        $username = $user->first_name . ' ' . $user->last_name;
        if ($user) {
            if (!$user->is_valid) {
                $user->is_valid = true;
                $user->save();
                Mail::to($email)->send(new AccountValidated($email, $username));
                return $user;

            } else {
                throw new Exception('User account already validated', ResponseAlias::HTTP_BAD_REQUEST);
            }
        }
        throw new Exception('User account not found', ResponseAlias::HTTP_NOT_FOUND);
    }

    /**
     * @param string $email
     * @return User
     * @throws Exception
     *
     */
    public final function suspendUserAccount(string $email): User
    {
        $user = User::where('email', $email)->first();
        if ($user) {
            if ($user->is_valid) {
                $user->is_valid = false;
                $user->save();
                Mail::to($user->email)->send(new AccountInvalidationMail($user));
                return $user;
            } else {
                throw new Exception('User account already not valid', ResponseAlias::HTTP_BAD_REQUEST);
            }
        }
        throw new Exception('User account not found', ResponseAlias::HTTP_NOT_FOUND);
    }

    /**
     * @param array $data
     * @return array
     */
    public final function createUserAccount(array $data): array
    {
        $profilePicture = $data['profile_picture'] ?? null;

        // user infos
        $user = User::create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'is_valid' => true,
        ]);

        // token for setting password
        $token = Str::random(60);
        $expires_at = now()->addDays(15);
        PasswordReset::create([
            'email' => $user->email,
            'token' => hash('sha256', $token),
            'created_at' => now(),
            'expires_at' => $expires_at,
        ]);
        $url = url("/set-password.blade.php/{$token}");
        Mail::to($user->email)->send(new SetPasswordMail($user->email, $url));

        // profile picture
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
     * @param string $email
     * @return void
     */
    public final function deleteUserAccount(string $email): void
    {
        $user = User::where('email', $email)->first();
        $user->delete();

    }

    /**
     * @param int $user_id
     * @param array $data
     * @return array
     */
    public final function updateUserAccount(int $user_id, array $data): array
    {
        $user = User::find($user_id);

        // Update basic user information
        unset($data['password']);
        $user->fill($data);
        $user->save();
        $profilePicture = $data['profile_picture'] ?? null;
        // Update profile picture if provided
        if ($profilePicture instanceof UploadedFile) {
            $media = MediaRepository::attachMediaToModel($user, $profilePicture, 'user');
            $fullUrl = $media->file_name;
        }

        return [
            'user_info' => $user,
            'profile_picture' => $fullUrl ?? null,
        ];
    }

}


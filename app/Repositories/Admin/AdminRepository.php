<?php

namespace App\Repositories\Admin;

use App\Mail\AccountInvalidationMail;
use App\Mail\AccountValidated;
use App\Mail\SetPasswordMail;
use App\Models\Admin;
use App\Models\PasswordResetToken;
use App\Models\User;
use App\Repositories\Media\MediaRepository;
use App\Traits\ErrorResponse;
use App\Traits\PaginationParams;
use App\Traits\SuccessResponse;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;


class AdminRepository
{
    use SuccessResponse, ErrorResponse, PaginationParams;

    /**
     * @param int $id
     * @return User
     * @throws Exception
     */
    public final function validateUserAccount(int $id): User
    {
        $user = User::where('id', $id)->first();
        $username = $user->first_name . ' ' . $user->last_name;
        if ($user) {
            if (!$user->is_valid) {
                $user->is_valid = true;
                $user->save();
                Mail::to($user->email)->send(new AccountValidated($user->email, $username));
                return $user;

            } else {
                throw new Exception(__('already_validated'), ResponseAlias::HTTP_BAD_REQUEST);
            }
        }
        throw new Exception(__('user_not_found'), ResponseAlias::HTTP_NOT_FOUND);
    }

    /**
     * @param int $id
     * @return User
     * @throws Exception
     */
    public final function suspendUserAccount(int $id): User
    {
        $user = User::where('id', $id)->first();
        if ($user) {
            if ($user->is_valid) {
                $user->is_valid = false;
                $user->save();
                Mail::to($user->email)->send(new AccountInvalidationMail($user));
                return $user;
            } else {
                throw new Exception(__('already_validated'), ResponseAlias::HTTP_BAD_REQUEST);
            }
        }
        throw new Exception(__('user_not_found'), ResponseAlias::HTTP_NOT_FOUND);
    }

    /**
     * @param array $data
     * @return User
     * @throws Exception
     */
    public final function createUserAccount(array $data): User
    {

        $user = User::create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'is_valid' => true,
        ]);
        if (!$user) {
            throw new Exception(__('general_error'), ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }

        // token for setting password
        self::createPasswordResetToken($user->email);
        $token = PasswordResetToken::where('email', $user->email)->first()->token;
        Mail::to($user->email)->send(new SetPasswordMail($token, $user->email));

        // profile picture
        $profilePicture = $data['profile_picture'] ?? null;
        if ($profilePicture instanceof UploadedFile) {
            MediaRepository::attachMediaToModel($user, $profilePicture);
        }
        return $user;
    }

    /**
     * @param $email
     * @return void
     */

    public static function createPasswordResetToken($email): void
    {
        PasswordResetToken::create([
            'email' => $email,
            'token' => hash('sha256', Str::random(60)),
            'created_at' => now(),
            'expires_at' => now()->addDays(15),
        ]);
    }

    /**
     * @param int $userId
     * @return void
     */
    public final function deleteUserAccount(int $userId): void
    {
        $user = User::where('id', $userId)->first();
        $user->with('media')->delete();

    }

    /**
     * @param int $accountId
     * @param array $data
     * @return Model
     * @throws Exception
     */
    public final function updateUserAccount(int $accountId, array $data): Model
    {

        $account = User::find($accountId);
        if (!$account) {
            throw new Exception(__('user_not_found'), ResponseAlias::HTTP_NOT_FOUND);
        }
        // Update basic user information
        unset($data['password']);
        $account->fill($data);
        $account->save();
        $currentMedia = $account->media->first();

        if ($currentMedia) {
            // update or add new profile picture
            if (array_key_exists('profile_picture', $data)) {
                MediaRepository::updateMediaFromModel($account, $data['profile_picture'], $currentMedia->id);
            } // remove profile picture
            else {
                MediaRepository::detachMediaFromModel($account, $currentMedia->id);
            }
        } elseif (array_key_exists('profile_picture', $data)) {
            MediaRepository::attachMediaToModel($account, $data['profile_picture']);
        }

        return $account;
    }

}


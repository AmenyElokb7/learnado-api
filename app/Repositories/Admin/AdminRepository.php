<?php

namespace App\Repositories\Admin;

use App\Mail\AccountInvalidationMail;
use App\Mail\AccountRejectionMail;
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

    /** create a user account with a password reset token and email the user
     * @param array $data
     * @return array
     * @throws Exception
     */
    public final function createUserAccount(array $data): array
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

        self::createPasswordResetToken($user->email);
        $token = PasswordResetToken::where('email', $user->email)->first()->token;
        Mail::to($user->email)->send(new SetPasswordMail($token, $user->email));

        $profilePicture = $data['profile_picture'] ?? null;
        if ($profilePicture instanceof UploadedFile) {
            MediaRepository::attachOrUpdateMediaForModel($user, $profilePicture);
        }
        return [$user,
            $user->media()->first(),];
    }

    /** create a password reset token
     * @param $email
     * @return void
     */

    public static function createPasswordResetToken($email): void
    {
        PasswordResetToken::create([
            'email' => $email,
            'token' => hash('sha256', Str::random(60)),
            'created_at' => now()->timestamp,
            'expires_at' => now()->addDays(15)->timestamp,
        ]);
    }

    /** delete a user account
     * @param int $userId
     * @return void
     * @throws Exception
     */
    public final function deleteUserAccount(int $userId): void
    {
        $user = User::where('id', $userId)->first();
        if ($user) {
            $user->media()->delete();
            $user->delete();
        }
        else throw new Exception(__('user_not_found'), ResponseAlias::HTTP_NOT_FOUND);

    }

    /** update a user account
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

        unset($data['password']);
        $account->fill($data);
        $account->save();

        $profilePicture = $data['profile_picture'] ?? null;

        if ($account->media->first()) {
            if ($profilePicture) {
                if ($profilePicture instanceof UploadedFile) {
                    MediaRepository::attachOrUpdateMediaForModel($account, $profilePicture, $account->media->first()->id ?? null);
                } else {
                    MediaRepository::detachMediaFromModel($account, $account->media->first()->id);
                }
            }
        } else {
            if ($profilePicture) {
                MediaRepository::attachOrUpdateMediaForModel($account, $profilePicture);
            }
        }

        return $account;
    }

    /** validate a user account
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

    /** suspend a user account
     * @param int $id
     * @return User
     * @throws Exception
     */
    public final function suspendUserAccount(int $id): User
    {
        $user = User::find($id);
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

    /** reject user account *
     * @param int $id
     * @return void
     * @throws Exception
     */

    public final function rejectUserAccount(int $id): void
    {
    $user = User::find($id);
    if (!$user) {
        throw new Exception(__('user_not_found'), ResponseAlias::HTTP_NOT_FOUND);
    }
        Mail::to($user->email)->send(new AccountRejectionMail($user));
    }
}

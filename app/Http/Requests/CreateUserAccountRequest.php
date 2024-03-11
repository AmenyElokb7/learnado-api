<?php

namespace App\Http\Requests;

use App\Enum\UserRoleEnum;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;


class CreateUserAccountRequest extends FormRequest
{


    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'first_name' => 'required|string|max:' . config('constants.MAX_STRING_LENGTH'),
            'last_name' => 'required|string|max:' . config('constants.MAX_STRING_LENGTH'),
            'email' => 'required|string|email|max:' . config('constants.MAX_STRING_LENGTH') . '|unique:users,email',
            'profile_picture' => 'sometimes|file|image|max:' . config('constants.MAX_FILE_SIZE') . '|mimes: ' . config('constants.MIME_TYPES'),
            'role' => 'required|int|in:' . UserRoleEnum::USER->value . ',' . UserRoleEnum::DESIGNER->value . ',' . UserRoleEnum::FACILITATOR->value,
        ];
    }

    /**
     * Calculate the maximum file size for validation.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'profile_picture.image' => __('messages.profile_picture_image'),
            'profile_picture.max' => __('messages.profile_picture_size') . config('constants.MAX_FILE_SIZE') / 1024 . 'MB',
            'profile_picture.mimes' => __('messages.profile_picture_type'),
            'first_name.required' => __('messages.first_name_required'),
            'first_name.string' => __('messages.first_name_string'),
            'first_name.max' => __('messages.first_name_max'),
            'last_name.required' => __('messages.last_name_required'),
            'last_name.string' => __('messages.last_name_string'),
            'last_name.max' => __('messages.last_name_max'),
            'email.required' => __('messages.email_required'),
            'email.email' => __('messages.email_email'),
            'email.unique' => __('messages.email_unique'),
            'role.required' => __('messages.account_type_required'),
        ];
    }

}

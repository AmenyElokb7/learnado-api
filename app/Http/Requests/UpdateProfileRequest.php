<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
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
            'first_name' => 'sometimes|string|max:' . config('constants.MAX_STRING_LENGTH'),
            'last_name' => 'sometimes|string|max:' . config('constants.MAX_STRING_LENGTH'),
            'password' => 'sometimes|string|min:' . config('constants.MIN_PASSWORD_LENGTH') . '|confirmed|regex:' . config('constants.PASSWORD_REGEX'),
            'profile_picture' => 'sometimes|file|image|max:' . config('constants.MAX_FILE_SIZE') . '|mimes: ' . config('constants.MIME_TYPES'),
            'deleted_files_id' => 'sometimes|int|exists:media,id',
        ];
    }

    public function messages()
    {
        return [
            'first_name.sometimes' => __('messages.first_name_required'),
            'first_name.max' => __('messages.first_name_max'),
            'last_name.sometimes' => __('messages.last_name_required'),
            'last_name.max' => __('messages.last_name_max'),
            'password.sometimes' => __('messages.password_required'),
            'password.min' => __('messages.password_min'),
            'password.regex' => __('messages.password_regex'),
            'password.confirmed' => __('messages.password_confirmed'),
        ];
    }
}

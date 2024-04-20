<?php

namespace App\Http\Requests;

use App\Enum\UserRoleEnum;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserAccountRequest extends FormRequest
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
            'first_name' => 'string|max:' . config('constants.MAX_STRING_LENGTH'),
            'last_name' => 'string|max:' . config('constants.MAX_STRING_LENGTH'),
            'role' => 'int|in:' . UserRoleEnum::USER->value . ',' . UserRoleEnum::DESIGNER->value . ',' . UserRoleEnum::FACILITATOR->value,
            'profile_picture' => 'sometimes|nullable|image|mimes:' . config('constants.MIME_TYPES') . '|max:' . config('constants.MAX_FILE_SIZE'),
        ];
    }

    public function messages(): array
    {
        return [
            'profile_picture.image' => __('messages.profile_picture_image'),
            'profile_picture.mimes' => __('messages.profile_picture_type' . config('constants.MIME_TYPES')),
            'profile_picture.max' => __('messages.profile_picture_size ' . config('constants.MAX_FILE_SIZE')),
            'first_name.sometimes' => __('messages.first_name_required'),
            'first_name.max' => __('messages.first_name_max'),
            'last_name.sometimes' => __('messages.last_name_required'),
            'last_name.max' => __('messages.last_name_max'),
            'role.required' => __('messages.account_type_required'),
            'role.in' => __('messages.account_type_invalid'),


        ];
    }
}

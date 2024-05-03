<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateStepRequest extends FormRequest
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
            'title' => 'sometimes|string|max:' . config('constants.MAX_STRING_LENGTH'),
            'description' => 'sometimes|string',
            'duration' => 'sometimes|integer',
            'media_files.*' => 'sometimes|file|mimes:' . config('constants.MEDIA_MIMES'),
            'media_to_remove.*' => 'sometimes|exists:media,id',
            'media_urls.*' => 'sometimes',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'title.sometimes' => __('messages.step_title_required'),
            'description.sometimes' => __('messages.step_description_required'),
            'duration.sometimes' => __('messages.step_duration_required'),
            'media_files.*.file' => __('messages.step_media_files_file'),
            'media_files.*.mimes' => __('messages.step_media_files_mimes'),
            'media_to_remove.*.exists' => __('messages.step_media_to_remove_valid'),
        ];
    }
}

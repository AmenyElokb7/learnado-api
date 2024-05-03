<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CreateCategoryRequest extends FormRequest
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
            'category' => 'required|string|unique:categories,category|max:' . config('constants.MAX_STRING_LENGTH'),
            'media' => 'required|file|image|max:' . config('constants.MAX_FILE_SIZE') . '|mimes:' . config('constants.MEDIA_MIMES'),
        ];
    }

    public function messages(): array
    {
        return [
            'category.required' => __('messages.category_required'),
            'category.unique' => __('messages.category_already_exists'),
            'category.max' => __('messages.category_max_length'),
            'media.file' => __('messages.media_file'),
            'media.image' => __('messages.media_image'),
            'media.max' => __('messages.media_max_size'),
            'media.mimes' => __('messages.media_mimes'),
        ];
    }
}

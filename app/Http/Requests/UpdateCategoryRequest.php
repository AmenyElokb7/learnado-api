<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'category' => 'required|string|unique:categories,category',
            'media' => 'required|file|image|max:' . config('constants.MAX_FILE_SIZE') . '|mimes: ' . config('constants.MIME_TYPES'),
        ];
    }

    public function messages(){
        return [
            'category.required' => __('messages.category_required'),
            'category.string' => __('messages.category_string'),
            'category.unique' => __('messages.category_unique'),
            'media.image' => __('messages.media_image'),
            'media.max' => __('messages.media_size') . config('constants.MAX_FILE_SIZE') / 1024 . 'MB',
            'media.mimes' => __('messages.media_type'),
        ];
    }
}

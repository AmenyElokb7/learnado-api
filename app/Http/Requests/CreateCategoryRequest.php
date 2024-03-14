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
        ];
    }

    public function messages(): array
    {
        return [
            'category.required' => __('category_required'),
            'category.unique' => __('category_already_exists'),
            'category.max' => __('category_max_length'),
        ];
    }
}

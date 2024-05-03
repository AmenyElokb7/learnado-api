<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CreateLanguageRequest extends FormRequest
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
            'language' => 'required|string|unique:languages,language|max:' . config('constants.MAX_STRING_LENGTH'),
        ];
    }

    public function messages(): array
    {
        return [
            'language.required' => __('language_required'),
            'language.unique' => __('language_already_exists'),
            'language.max' => __('language_max_length'),
        ];
    }


}

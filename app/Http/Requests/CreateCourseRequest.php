<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CreateCourseRequest extends FormRequest
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
            'title' => 'required|string',
            'category' => 'required|string',
            'description' => 'required|string',
            'prerequisites' => 'string',
            'course_for' => 'string',
            'language' => 'required',
            'is_paid' => 'required|boolean',
            'price' => [
                'numeric',
                function ($attribute, $value, $fail) {
                    if ($this->input('is_paid') && (!$value && $value !== '0' && $value !== 0)) {
                        $fail(__('messages.course_price_required'));
                    }
                },
            ], 'discount' => 'required|numeric',
            'course_media.*' => 'file|image|max:' . config('constants.MAX_FILE_SIZE') . '|mimes: ' . config('constants.MIME_TYPES'),
            'course_media' => 'array',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => __('validation.first_name_required'),
            'category.required' => __('validation.last_name_required'),
            'description.required' => __('validation.email_required'),
            'prerequisites.string' => __('validation.email_unique'),
            'language.required' => __('validation.password_confirmed'),
            'duration.required' => __('validation.password_confirmed'),
            'is_paid.required' => __('validation.password_confirmed'),
            'is_paid.boolean' => __('validation.password_confirmed'),
            'price.required' => __('validation.password_confirmed'),
            'price.numeric' => __('validation.password_confirmed'),
            'discount.required' => __('validation.password_confirmed'),
            'discount.numeric' => __('validation.password_confirmed'),
            'course_media.*.file' => __('validation.profile_picture_image'),
            'course_media.*.mimes' => __('validation.profile_picture_type'),
            'course_media.*.max' => __('validation.profile_picture_size'),
            'course_media.array' => __('validation.profile_picture_size'),
        ];
    }
}

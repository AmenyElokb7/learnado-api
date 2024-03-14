<?php

namespace App\Http\Requests;

use App\Enum\TeachingTypeEnum;
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
            'title' => 'required|string|max:' . config('constants.MAX_STRING_LENGTH'),
            'category' => 'required|int:exists:categories,id',
            'description' => 'required|string',
            'language' => 'required|int:exists:languages,id',
            'is_paid' => 'required|boolean',
            'price' => 'nullable|numeric|required_if:is_paid,true|min:' . config('constants.CURRENCY_MIN_VALUE'),
            'discount' => 'nullable|numeric|min:' . config('constants.CURRENCY_MIN_VALUE'),
            'facilitator_id' => 'nullable|exists:users,id',
            'is_public' => 'required|boolean',
            'selectedUserIds' => 'required_if:is_public,false|array',
            'selectedUserIds.*' => 'exists:users,id',
            'course_media' => 'nullable|array',
            'course_media.*' => 'file|image|max:' . config('constants.MAX_FILE_SIZE') . '|mimes:' . config('constants.MEDIA_MIMES'),
            'teaching_type' => 'nullable|integer',
            'link' => 'required_if:teaching_type,' . TeachingTypeEnum::ONLINE->value . '|nullable|string',
            'start_time' => 'required_if:teaching_type,' . TeachingTypeEnum::ONLINE->value . '|nullable|date',
            'end_time' => 'required_if:teaching_type,' . TeachingTypeEnum::ONLINE->value . '|nullable|date',
            'latitude' => 'required_if:teaching_type,' . TeachingTypeEnum::ON_A_PLACE->value . '|nullable|string',
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
            'title.required' => __('messages.course_title_required'),
            'category.required' => __('messages.course_category_required'),
            'description.required' => __('messages.course_description_required'),
            'language.required' => __('messages.course_language_required'),
            'is_paid.required' => __('messages.course_is_paid_required'),
            'price.required_if' => __('messages.course_price_required_if'),
            'facilitator_id.exists' => __('messages.facilitator_id_exists'),
            'selectedUserIds.required_if' => __('messages.selectedUserIds_required_if'),
            'selectedUserIds.*.exists' => __('messages.selectedUserIds_exists'),
            'course_media.*.max' => __('messages.course_media_max'),
            'course_media.*.mimes' => __('messages.course_media_mimes'),
            'latitude.required_if' => __('messages.place_required_if'),
            'link.required_if' => __('messages.link_required_if'),

        ];
    }
}

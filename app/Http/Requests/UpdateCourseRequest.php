<?php

namespace App\Http\Requests;

use App\Enum\TeachingTypeEnum;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCourseRequest extends FormRequest
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
            'category_id' => 'sometimes|exists:categories,id',
            'description' => 'sometimes|string',
            'language_id' => 'sometimes|exists:languages,id',
            'is_paid' => 'sometimes|boolean',
            'price' => 'nullable|numeric|required_if:is_paid,true|min:' . config('constants.CURRENCY_MIN_VALUE'),
            'discount' => 'nullable|numeric|min:' . config('constants.CURRENCY_MIN_VALUE'),
            'facilitator_id' => 'nullable|exists:users,id',
            'is_public' => 'sometimes|boolean',
            'selected_user_ids' => 'required_if:is_public,false|string',
            'selected_user_ids.*' => 'exists:users,id',
            'course_media' => 'file|max:' . config('constants.MAX_FILE_SIZE') . '|mimes:' . config('constants.MEDIA_MIMES'),
            'teaching_type' => 'nullable|integer',
            'link' => 'required_if:teaching_type,' . TeachingTypeEnum::ONLINE->value . '|nullable|string',
            'start_time' => 'nullable|required_if:teaching_type,' . TeachingTypeEnum::ONLINE->value . '|integer',
            'end_time' => 'nullable|required_if:teaching_type,' . TeachingTypeEnum::ONLINE->value . '|integer',
            'latitude' => 'required_if:teaching_type,' . TeachingTypeEnum::ON_A_PLACE->value . '|nullable|string',
            'has_forum' => 'sometimes|boolean',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'title.sometimes' => __('messages.course_title_required'),
            'category_id.sometimes' => __('messages.course_category_required'),
            'description.sometimes' => __('messages.course_description_required'),
            'language_id.sometimes' => __('messages.course_language_required'),
            'is_paid.sometimes' => __('messages.course_is_paid_required'),
            'price.required_if' => __('messages.course_price_required_if'),
            'facilitator_id.exists' => __('messages.facilitator_id_exists'),
            'selected_user_ids.required_if' => __('messages.selectedUserIds_required_if'),
            'selected_user_ids.*.exists' => __('messages.selectedUserIds_exists'),
            'course_media' => 'nullable|array',
            'course_media.*' => 'file|image|max:' . config('constants.MAX_FILE_SIZE') . '|mimes:' . config('constants.MIME_TYPES'),
            'latitude.required_if' => __('messages.latitude_required_if'),
            'link.required_if' => __('messages.link_required_if'),
            'start_time.required_if' => __('messages.start_time_required_if'),
            'end_time.required_if' => __('messages.end_time_required_if'),
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateLearningPathRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'sometimes|required|string',
            'description' => 'sometimes|required|string',
            'course_ids' => 'nullable|array',
            'course_ids.*' => 'exists:courses,id',
            'media_files' => 'nullable|array',
            'media_files.*' => 'file|image|mimes:' . config('constants.MIME_TYPES') . '|max:' . config('constants.MAX_FILE_SIZE'),
            'media_to_remove' => 'nullable|array',
            'media_to_remove.*' => 'exists:media,id',
        ];
    }
}

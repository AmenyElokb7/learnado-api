<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CreateLearningPathRequest extends FormRequest
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
            'description' => 'required|string',
            'language_id' => 'required|exists:languages,id',
            'category_id' => 'required|exists:categories,id',
            'course_ids' => 'nullable|array',
            'course_ids.*' => 'exists:courses,id',
            'quiz.description' => 'nullable|string',
            'quiz.questions' => 'nullable|array',
            'quiz.questions.*.question' => 'required_with:quiz.questions|string',
            'quiz.questions.*.type' => 'required_with:quiz.questions|string',
            'quiz.questions.*.is_valid' => 'nullable|boolean',
            'quiz.questions.*.answers' => 'nullable|array',
            'quiz.questions.*.answers.*.answer' => 'required_with:quiz.questions.*.answers|string',
            'quiz.questions.*.answers.*.is_valid' => 'required_with:quiz.questions.*.answers|boolean',
            'media_files' => 'nullable|array',
            'media_files.*' => 'file|image|mimes:' . config('constants.MIME_TYPES') . '|max:' . config('constants.MAX_FILE_SIZE'),

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
            'title.required' => 'Title is required',
            'description.required' => 'Description is required',
            'language_id.required' => 'Language is required',
            'category_id.required' => 'Category is required',
            'course_ids.*.exists' => 'Course does not exist',
            'quiz.questions.*.question.required' => 'Question is required',
            'quiz.questions.*.type.required' => 'Type is required',
            'quiz.questions.*.answers.*.answer.required' => 'Answer is required',
            'quiz.questions.*.answers.*.is_valid.required' => 'Is valid is required',

        ];
    }
}

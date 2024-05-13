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
            'courses' => 'nullable|array',
            'courses.*' => 'exists:courses,id',
            'price' => 'nullable|numeric',
            'has_forum' => 'required|boolean',
            'is_public' => 'required|boolean',
            'quiz' => 'sometimes|array',
            'quiz.questions.*.question' => 'required_with:quiz|string',
            'quiz.questions.*.type' => 'required_with:quiz|string|in:BINARY,QCM,OPEN',
            'quiz.questions.*.is_valid' => 'required_if:quiz.questions.*.type,BINARY|boolean',
            'quiz.questions.*.answers.*.answer' => 'required_if:quiz.questions.*.type,QCM|required_if:quiz.questions.*.type,OPEN|string',
            'quiz.questions.*.answers.*.is_valid' => 'required_if:quiz.questions.*.type,QCM|boolean',
            'media_file' => 'sometimes|file|max:' . config('constants.MAX_FILE_SIZE') . '|mimes:' . config('constants.MEDIA_MIMES'),
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
            'title.required' => __('messages.learning_path_title_required'),
            'description.required' => __('messages.learning_path_description_required'),
            'language_id.required' => __('messages.learning_path_language_required'),
            'category_id.required' => __('messages.learning_path_category_required'),
            'course_ids.*.exists' => __('messages.learning_path_course_ids_exists'),
            'price.numeric' => __('messages.learning_path_price_numeric'),
            'has_forum.required' => __('messages.learning_path_has_forum_required'),
            'is_public.required' => __('messages.learning_path_is_public_required'),
            'quiz.questions.*.question.required_with' => __('messages.learning_path_quiz_questions_question_required_with'),
            'quiz.questions.*.question.string' => __('messages.learning_path_quiz_questions_question_string'),
            'quiz.questions.*.type.required_with' => __('messages.learning_path_quiz_questions_type_required_with'),
            'quiz.questions.*.type.string' => __('messages.learning_path_quiz_questions_type_string'),
            'quiz.questions.*.type.in' => __('messages.learning_path_quiz_questions_type_in'),
            'quiz.questions.*.is_valid.required_if' => __('messages.learning_path_quiz_questions_is_valid_required_if'),
            'quiz.questions.*.is_valid.boolean' => __('messages.learning_path_quiz_questions_is_valid_boolean'),
            'quiz.questions.*.answers.*.answer.required_if' => __('messages.learning_path_quiz_questions_answers_answer_required_if'),
            'quiz.questions.*.answers.*.answer.string' => __('messages.learning_path_quiz_questions_answers_answer_string'),
            'quiz.questions.*.answers.*.is_valid.required_if' => __('messages.learning_path_quiz_questions_answers_is_valid_required_if'),
            'quiz.questions.*.answers.*.is_valid.boolean' => __('messages.learning_path_quiz_questions_answers_is_valid_boolean'),
            'media_file.file' => __('messages.learning_path_media_file_file'),
            'media_file.max' => __('messages.learning_path_media_file_max'),
            'media_file.mimes' => __('messages.learning_path_media_file_mimes'),

        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CreateStepRequest extends FormRequest
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
            'steps' => 'required|array',
            'steps.*.title' => 'required|string|max:' . config('constants.MAX_STRING_LENGTH'),
            'steps.*.description' => 'required|string',
            'steps.*.duration' => 'required|integer',
            'steps.*.media_files.*' => 'sometimes|file|mimes:' . config('constants.MEDIA_MIMES'),
            'steps.*.quiz' => 'sometimes|array',
            'steps.*.quiz.questions.*.question' => 'required_with:steps.*.quiz|string',
            'steps.*.quiz.questions.*.type' => 'required_with:steps.*.quiz|string|in:BINARY,QCM,OPEN',
            'steps.*.quiz.questions.*.is_valid' => 'required_if:steps.*.quiz.questions.*.type,BINARY|boolean',
            'steps.*.quiz.questions.*.answers.*.answer' => 'required_if:steps.*.quiz.questions.*.type,QCM|required_if:steps.*.quiz.questions.*.type,OPEN|string',
            'steps.*.quiz.questions.*.answers.*.is_valid' => 'required_if:steps.*.quiz.questions.*.type,QCM|boolean',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'steps.required' => __('messages.step_required'),
            'steps.*.title.required' => __('messages.step_title_required'),
            'steps.*.description.required' => __('messages.step_description_required'),
            'steps.*.duration.required' => __('messages.step_duration_required'),
            'steps.*.duration.integer' => __('messages.step_duration_integer'),
            'steps.*.media_files.*.file' => __('messages.step_media_files_file'),
            'steps.*.media_files.*.mimes' => __('messages.step_media_files_mimes'),
            'steps.*.quiz.required' => __('messages.step_quiz_required'),
            'steps.*.quiz.questions.*.question.required_with' => __('messages.step_quiz_questions_question_required_with'),
            'steps.*.quiz.questions.*.question.string' => __('messages.step_quiz_questions_question_string'),
            'steps.*.quiz.questions.*.type.required_with' => __('messages.step_quiz_questions_type_required_with'),
            'steps.*.quiz.questions.*.type.string' => __('messages.step_quiz_questions_type_string'),
            'steps.*.quiz.questions.*.type.in' => __('messages.step_quiz_questions_type_in'),
            'steps.*.quiz.questions.*.is_valid.required_if' => __('messages.step_quiz_questions_is_valid_required_if'),
            'steps.*.quiz.questions.*.is_valid.boolean' => __('messages.step_quiz_questions_is_valid_boolean'),
            'steps.*.quiz.questions.*.answers.*.answer.required_if' => __('messages.step_quiz_questions_answers_answer_required_if'),
            'steps.*.quiz.questions.*.answers.*.answer.string' => __('messages.step_quiz_questions_answers_answer_string'),
            'steps.*.quiz.questions.*.answers.*.is_valid.required_if' => __('messages.step_quiz_questions_answers_is_valid_required_if'),
            'steps.*.quiz.questions.*.answers.*.is_valid.boolean' => __('messages.step_quiz_questions_answers_is_valid_boolean'),
        ];
    }
}

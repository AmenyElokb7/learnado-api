<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateQuizRequest extends FormRequest
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
            'quiz.questions.*.id' => 'sometimes|exists:questions,id',
            'quiz.questions.*.question' => 'sometimes|required_with:quiz.questions|string',
            'quiz.questions.*.type' => 'sometimes|required_with:quiz.questions|string|in:BINARY,QCM,OPEN',
            'quiz.questions.*.is_valid' => 'sometimes|required_if:quiz.questions.*.type,binary|boolean',
            'quiz.questions.*.answers.*.id' => 'sometimes|exists:answers,id',
            'quiz.questions.*.answers.*.answer' => 'sometimes|required_if:quiz.questions.*.type,QCM|string',
            'quiz.questions.*.answers.*.is_valid' => 'sometimes|required_if:quiz.questions.*.type,QCM|boolean',
        ];
    }

    /**
     * Get the validation messages that apply to the request.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'quiz.title.string' => __('quiz_title_string'),
            'quiz.title.max' => __('quiz_title_max'),
            'quiz.questions.*.id.exists' => __('quiz_questions_id_exists'),
            'quiz.questions.*.question.required_with' => __('quiz_questions_question_required_with'),
            'quiz.questions.*.question.string' => __('quiz_questions_question_string'),
            'quiz.questions.*.type.required_with' => __('quiz_questions_type_required_with'),
            'quiz.questions.*.type.string' => __('quiz_questions_type_string'),
            'quiz.questions.*.type.in' => __('quiz_questions_type_in'),
            'quiz.questions.*.is_valid.required_if' => __('quiz_questions_is_valid_required_if'),
            'quiz.questions.*.is_valid.boolean' => __('quiz_questions_is_valid_boolean'),
            'quiz.questions.*.answers.*.id.exists' => __('quiz_questions_answers_id_exists'),
            'quiz.questions.*.answers.*.answer.required_if' => __('quiz_questions_answers_answer_required_if'),
            'quiz.questions.*.answers.*.answer.string' => __('quiz_questions_answers_answer_string'),
            'quiz.questions.*.answers.*.is_valid.required_if' => __('quiz_questions_answers_is_valid_required_if'),
            'quiz.questions.*.answers.*.is_valid.boolean' => __('quiz_questions_answers_is_valid_boolean'),
        ];
    }
}

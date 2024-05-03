<?php

namespace App\Http\Requests;

use App\Enum\UserRoleEnum;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;

class RegistrationRequest extends FormRequest
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
            'first_name' => 'required|string|max:' . config('constants.MAX_STRING_LENGTH'),
            'last_name' => 'required|string|max:' . config('constants.MAX_STRING_LENGTH'),
            'email' => 'required|string|email|unique:users,email|max:' . config('constants.MAX_STRING_LENGTH'),
            'password' => 'required|string|min:' . config('constants.MIN_PASSWORD_LENGTH') . '|confirmed|regex:' . config('constants.PASSWORD_REGEX'),
            'role' => 'required|int|in:' . UserRoleEnum::USER->value . ',' . UserRoleEnum::DESIGNER->value . ',' . UserRoleEnum::FACILITATOR->value,
        ];
    }

    public function messages() : array
    {
        return [
            'first_name.required' => __('messages.first_name_required'),
            'first_name.max' => __('messages.first_name_max'),
            'last_name.required' => __('messages.last_name_required'),
            'last_name.max' => __('messages.last_name_max'),
            'email.required' => __('messages.email_required'),
            'email.email' => __('messages.email_email'),
            'email.unique' => __('messages.email_unique'),
            'password.required' => __('messages.password_required'),
            'password.min' => __('messages.password_min'),
            'password.regex' => __('messages.password_regex'),
            'password.confirmed' => __('messages.password_confirmed'),
        ];
    }
    protected function failedValidation(Validator $validator)
    {
        // Get the validation errors
        $errors = $validator->errors();

        // Customize the response format
        $customResponse = [];

        foreach ($errors->getMessages() as $field => $message) {
            // Here you can customize the response as needed
            $customResponse[$field] = $message[0]; // Just an example to return the first error message
        }

        // Throw an HttpResponseException with your custom response
        throw new HttpResponseException(response()->json([
            'status' => Response::HTTP_UNPROCESSABLE_ENTITY,

            'errors' => $customResponse
        ], Response::HTTP_UNPROCESSABLE_ENTITY));
    }
}

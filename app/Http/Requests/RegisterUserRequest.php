<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\User;

class RegisterUserRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255'],
            'dob'      => ['required_if:role,provider', 'nullable', 'date', 'before:today'],
            'phone'    => ['required', 'string', 'max:15'],
            'languages' => ['required_if:role,seeker', 'string'],
            'password' => ['required', 'string', 'min:8', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*\W).+$/'],
            'gender'   => ['required_if:role,provider', 'nullable', 'in:male,female'],
            'role'     => ['required', 'in:provider,seeker'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Check email
            $emailUser = User::where('email', $this->email)->first();

            if ($emailUser && $emailUser->hasRole($this->role)) {
                $validator->errors()->add(
                    'email',
                    "This email is already registered as a {$this->role}."
                );
            }

            // Check phone
            $phoneUser = User::where('phone', $this->phone)->first();

            if ($phoneUser && $phoneUser->hasRole($this->role)) {
                $validator->errors()->add(
                    'phone',
                    "This phone number is already registered as a {$this->role}."
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'dob.before' => 'Date of birth must be in the past.',
            'role.in'    => 'Role must be either provider or seeker.',
            'email.unique' => 'This email is already registered.',
            'gender.in' => 'Gender must be either male or female.',
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.'
        ];
    }
}

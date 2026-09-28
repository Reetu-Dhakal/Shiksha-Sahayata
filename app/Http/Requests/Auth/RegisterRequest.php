<?php

namespace App\Http\Requests\Auth;

use App\Enums\Role;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'account_type' => ['required', 'in:student,guardian'],
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required_without:phone', 'nullable', 'email', 'max:150', 'unique:users,email'],
            'phone' => ['required_without:email', 'nullable', 'string', 'max:20', 'unique:users,phone', 'regex:/^[0-9+\-\s]{7,20}$/'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.required_without' => 'Please provide an email address or a phone number.',
            'phone.required_without' => 'Please provide a phone number or an email address.',
            'phone.regex' => 'Please enter a valid phone number (digits, +, - or spaces only).',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'account_type' => 'account type',
        ];
    }

    public function accountRole(): Role
    {
        return $this->input('account_type') === 'guardian' ? Role::GUARDIAN : Role::STUDENT;
    }
}

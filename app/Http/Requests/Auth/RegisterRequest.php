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

    public function accountRole(): Role
    {
        return $this->input('account_type') === 'guardian' ? Role::GUARDIAN : Role::STUDENT;
    }
}

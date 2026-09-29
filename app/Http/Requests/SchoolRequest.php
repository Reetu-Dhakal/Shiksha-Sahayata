<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SchoolRequest extends FormRequest
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
        $schoolId = $this->route('school')?->id;

        return [
            'name' => ['required', 'string', 'max:150'],
            'school_code' => ['nullable', 'string', 'max:30', Rule::unique('schools', 'school_code')->ignore($schoolId)],
            'province' => ['required', 'string', 'max:60'],
            'district' => ['required', 'string', 'max:60'],
            'municipality' => ['required', 'string', 'max:60'],
            'address' => ['nullable', 'string', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s]{7,20}$/'],
            'email' => ['nullable', 'email', 'max:150'],
            'status' => ['required', 'in:ACTIVE,INACTIVE'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'contact_phone.regex' => __('admin.validation.school_contact_phone_regex'),
        ];
    }
}

<?php

namespace App\Http\Requests;

use App\Enums\EducationLevel;
use App\Enums\Gender;
use App\Enums\StudentCategory;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StudentProfileRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:150'],
            'name_np' => ['nullable', 'string', 'max:150'],
            'date_of_birth' => ['required', 'date', 'before:today', 'after:1940-01-01'],
            'gender' => ['required', Rule::enum(Gender::class)],
            'education_level' => ['required', Rule::enum(EducationLevel::class)],
            'grade' => ['required', 'integer', 'min:1', 'max:12'],
            'province' => ['required', 'string', 'max:60'],
            'district' => ['required', 'string', 'max:60'],
            'municipality' => ['required', 'string', 'max:60'],
            'student_category' => ['nullable', Rule::enum(StudentCategory::class)],
            'birth_registration_number' => ['nullable', 'string', 'max:50'],
            'school_id' => ['required', 'integer', 'exists:schools,id'],
            'guardian_name' => ['required', 'string', 'max:150'],
            'guardian_relationship' => ['required', 'string', 'max:50'],
            'guardian_phone' => ['required', 'string', 'max:20', 'regex:/^[0-9+\-\s]{7,20}$/'],
            'guardian_citizenship_number' => ['nullable', 'string', 'max:40'],
            'guardian_address' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'guardian_phone.regex' => 'Please enter a valid guardian phone number (digits, +, - or spaces only).',
            'grade.min' => 'Grade must be between 1 and 12.',
            'grade.max' => 'Grade must be between 1 and 12.',
        ];
    }
}

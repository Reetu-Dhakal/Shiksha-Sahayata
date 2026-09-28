<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $isStore = $this->isMethod('POST');

        return [
            'scholarship_id' => $isStore
                ? ['required', 'integer', 'exists:scholarships,id']
                : ['sometimes', 'nullable', 'integer', 'exists:scholarships,id'],
            'student_id' => ['sometimes', 'nullable', 'integer', 'exists:students,id'],
            'statement' => ['required', 'string', 'min:80', 'max:4000'],
            'previous_school' => ['nullable', 'string', 'max:150'],
            'current_grade' => ['nullable', 'integer', 'between:1,12'],
            'grade_point_average' => ['nullable', 'numeric', 'between:0,4'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'statement.min' => 'Please write at least 80 characters explaining your need and goals.',
        ];
    }
}

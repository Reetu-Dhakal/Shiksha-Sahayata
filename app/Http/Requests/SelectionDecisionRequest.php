<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SelectionDecisionRequest extends FormRequest
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
        return [
            'decision' => ['required', Rule::in(['SELECTED', 'WAITLISTED', 'REJECTED'])],
            'reason' => ['required', 'string', 'min:10', 'max:2000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'reason.min' => 'The decision reason must be at least 10 characters — applicants can see it.',
        ];
    }
}

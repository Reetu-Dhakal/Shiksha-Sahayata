<?php

namespace App\Http\Requests;

use App\Enums\DocumentType;
use App\Enums\EducationLevel;
use App\Enums\EligibilityField;
use App\Enums\EligibilityOperator;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ScholarshipRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:150'],
            'description' => ['required', 'string', 'max:5000'],
            'provider' => ['required', 'string', 'max:150'],
            'application_start' => ['required', 'date'],
            'application_deadline' => ['required', 'date', 'after_or_equal:application_start'],
            'education_level' => ['nullable', Rule::enum(EducationLevel::class)],
            'target_grade_min' => ['nullable', 'integer', 'min:1', 'max:12'],
            'target_grade_max' => ['nullable', 'integer', 'min:1', 'max:12', 'gte:target_grade_min'],
            'available_slots' => ['nullable', 'integer', 'min:1', 'max:10000'],

            'criteria' => ['nullable', 'array'],
            'criteria.*.name' => ['required_with:criteria', 'string', 'max:100'],
            'criteria.*.description' => ['nullable', 'string', 'max:255'],
            'criteria.*.weight' => ['required_with:criteria', 'numeric', 'min:0.1', 'max:100'],
            'criteria.*.maximum_score' => ['required_with:criteria', 'integer', 'min:1', 'max:1000'],

            'documents' => ['nullable', 'array'],
            'documents.*.document_type' => ['required_with:documents', Rule::enum(DocumentType::class)],
            'documents.*.description' => ['nullable', 'string', 'max:255'],
            'documents.*.is_required' => ['nullable', 'boolean'],

            'rules' => ['nullable', 'array'],
            'rules.*.field' => ['required_with:rules', Rule::enum(EligibilityField::class)],
            'rules.*.operator' => ['required_with:rules', Rule::enum(EligibilityOperator::class)],
            'rules.*.value' => ['required_with:rules', 'string', 'max:255'],
            'rules.*.description' => ['nullable', 'string', 'max:255'],

            'committee' => ['nullable', 'array'],
            'committee.*' => ['integer', 'exists:users,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'application_deadline.after_or_equal' => 'The deadline must be on or after the start date.',
            'target_grade_max.gte' => 'The maximum grade must be greater than or equal to the minimum grade.',
            'criteria.*.weight.max' => 'Each criterion weight must be 100 or less.',
        ];
    }
}

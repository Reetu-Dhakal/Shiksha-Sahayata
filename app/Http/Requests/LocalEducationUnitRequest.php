<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class LocalEducationUnitRequest extends FormRequest
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
        $unitId = $this->route('local_education_unit')?->id;

        return [
            'name' => ['required', 'string', 'max:150'],
            'province' => ['required', 'string', 'max:60'],
            'district' => ['required', 'string', 'max:60'],
            'municipality' => ['required', 'string', 'max:60'],
            'contact_information' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:ACTIVE,INACTIVE'],
        ];
    }
}

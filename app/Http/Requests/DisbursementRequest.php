<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DisbursementRequest extends FormRequest
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
            'disbursement_status' => [
                'required',
                Rule::in(['PROCESSING', 'RELEASED', 'RECEIVED', 'CONFIRMED']),
            ],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ];
    }
}

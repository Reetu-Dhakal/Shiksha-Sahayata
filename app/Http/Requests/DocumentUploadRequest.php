<?php

namespace App\Http\Requests;

use App\Models\Application;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DocumentUploadRequest extends FormRequest
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
        /** @var Application $application */
        $application = $this->route('application');

        $allowed = $application->scholarship->requiredDocuments->pluck('document_type')->all();

        return [
            'document_type' => [
                'required',
                'string',
                Rule::in($allowed),
            ],
            'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'file.mimes' => 'Only PDF, JPG or PNG files are accepted.',
            'file.max' => 'The file must not be larger than 5 MB.',
        ];
    }
}

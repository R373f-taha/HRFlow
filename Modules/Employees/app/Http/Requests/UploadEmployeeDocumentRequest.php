<?php

namespace Modules\Employees\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadEmployeeDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorized via Controller Policy
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'string', 'max:100'],
            'document' => [
                'required',
                'file',
                'mimes:pdf,jpg,jpeg,png,doc,docx',
                'max:10240', // Max size: 10 MB (10240 KB)
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'document.mimes' => 'Only PDF, JPEG, PNG, DOC, and DOCX documents are allowed.',
            'document.max' => 'Document size must not exceed 10 MB.',
        ];
    }
}

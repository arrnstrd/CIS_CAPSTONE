<?php

namespace App\Http\Requests\Import;

use Illuminate\Foundation\Http\FormRequest;

class UploadImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'extensions:xlsx,xls',
                'mimetypes:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel',
                'max:10240', // 10 MB
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required'    => 'Please select an Excel file to upload.',
            'file.file'        => 'The uploaded file is invalid.',
            'file.extensions'  => 'Only .xlsx or .xls files are accepted.',
            'file.mimetypes'   => 'The file must be a valid Excel spreadsheet (.xlsx or .xls).',
            'file.max'         => 'File size must not exceed 10 MB.',
        ];
    }
}

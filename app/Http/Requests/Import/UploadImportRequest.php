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
                'extensions:xlsx',
                'mimetypes:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'max:10240', // 10 MB
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required'    => 'Please select an Excel file to upload.',
            'file.file'        => 'The uploaded file is invalid.',
            'file.extensions'  => 'Only .xlsx files are accepted.',
            'file.mimetypes'   => 'The file must be a valid Excel spreadsheet.',
            'file.max'         => 'File size must not exceed 10 MB.',
        ];
    }
}

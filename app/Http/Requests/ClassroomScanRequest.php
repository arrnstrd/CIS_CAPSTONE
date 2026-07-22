<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClassroomScanRequest extends FormRequest
{
    /**
     * Authorization (ownership of the teaching_assignment_id) is verified
     * in the service layer against the authenticated teacher, not here —
     * this request only validates shape/presence of the input.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'qr_code' => ['required', 'string'],
            'teaching_assignment_id' => ['required', 'integer', 'exists:teaching_assignments,id'],
        ];
    }
}

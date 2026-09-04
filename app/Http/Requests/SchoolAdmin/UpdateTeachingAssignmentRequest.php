<?php

namespace App\Http\Requests\SchoolAdmin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTeachingAssignmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Note: The composite uniqueness constraint 'teaching_assignments_unique' on
        // (teacher_id, subject_id, section_id, school_year_id) is enforced at the Service layer (Step 5).
        return [
            'teacher_id' => ['required', 'exists:teachers,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'section_id' => ['required', 'exists:sections,id'],
            'school_year_id' => ['required', 'exists:school_years,id'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }
}

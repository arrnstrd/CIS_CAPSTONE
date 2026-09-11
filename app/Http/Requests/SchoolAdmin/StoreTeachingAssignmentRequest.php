<?php

namespace App\Http\Requests\SchoolAdmin;

use App\Models\Section;
use App\Models\Subject;
use Illuminate\Foundation\Http\FormRequest;

class StoreTeachingAssignmentRequest extends FormRequest
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
            'section_id' => ['required', 'exists:sections,id'],
            'school_year_id' => ['required', 'exists:school_years,id'],
            'status' => ['required', 'in:active,inactive'],

            'subject_id' => ['required_without:subject_ids', 'nullable', 'exists:subjects,id'],
            'subject_ids' => ['required_without:subject_id', 'nullable', 'array', 'min:1'],
            'subject_ids.*' => ['required', 'integer', 'distinct', 'exists:subjects,id'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $sectionId = $this->input('section_id');
            if (!$sectionId) {
                return;
            }

            $section = Section::find($sectionId);
            if (!$section) {
                return;
            }

            $isPrimary = in_array((int) $section->grade_level, [1, 2, 3], true);

            if ($this->filled('subject_ids')) {
                if (!$isPrimary) {
                    $validator->errors()->add(
                        'subject_ids',
                        'Mass subject assignment is only available for primary grades (Grades 1 to 3).'
                    );
                } else {
                    $invalidSubjects = Subject::whereIn('id', $this->input('subject_ids', []))
                        ->where('level', '!=', 'elementary')
                        ->count();

                    if ($invalidSubjects > 0) {
                        $validator->errors()->add(
                            'subject_ids',
                            'All selected subjects must belong to the elementary level for primary grades.'
                        );
                    }
                }
            }
        });
    }
}

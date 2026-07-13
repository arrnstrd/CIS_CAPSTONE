<?php

namespace App\Http\Requests\Grading\QuarterlyGrade;

use Illuminate\Foundation\Http\FormRequest;

class UpdateQuarterlyGradeRequest extends FormRequest
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
        // Note: Grade fields (written_work_grade, performance_task_grade, quarterly_assessment_grade,
        // initial_grade, transmuted_grade) are system-computed per Business Rules in DATABASE.md
        // and should NOT be part of the validated input in Store/Update requests used by teacher-facing forms.
        return [
            'teaching_assignment_id' => ['required', 'exists:teaching_assignments,id'],
            'enrollment_id' => ['required', 'exists:enrollments,id'],
            'grading_period_id' => ['required', 'exists:grading_periods,id'],
        ];
    }
}

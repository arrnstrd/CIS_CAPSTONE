<?php

namespace App\Http\Requests\Grading\Assessment;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAssessmentRequest extends FormRequest
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
        return [
            'teaching_assignment_id' => ['required', 'exists:teaching_assignments,id'],
            'assessment_category_id' => ['required', 'exists:assessment_categories,id'],
            'grading_period_id' => ['required', 'exists:grading_periods,id'],
            'title' => ['required', 'string', 'max:255'],
            'total_items' => ['required', 'integer', 'min:1'],
            'assessment_date' => ['required', 'date'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }
}

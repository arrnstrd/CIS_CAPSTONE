<?php

namespace App\Http\Requests\Grading\StudentScore;

use App\Models\Assessment;
use Illuminate\Foundation\Http\FormRequest;

class StoreStudentAssessmentScoreRequest extends FormRequest
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
            'assessment_id' => ['required', 'exists:assessments,id'],
            'enrollment_id' => ['required', 'exists:enrollments,id'],
            'score' => [
                'required',
                'numeric',
                'min:0',
                function (string $attribute, mixed $value, \Closure $fail) {
                    $assessmentId = $this->input('assessment_id');
                    if ($assessmentId) {
                        $assessment = Assessment::find($assessmentId);
                        if ($assessment && $value > $assessment->total_items) {
                            $fail("The score cannot exceed the total items of the assessment ({$assessment->total_items}).");
                        }
                    }
                }
            ],
            'remarks' => ['nullable', 'string', 'max:255'],
        ];
    }
}

<?php

namespace App\Http\Requests\Grading\StudentScore;

use App\Models\Assessment;
use App\Models\StudentAssessmentScore;
use Illuminate\Foundation\Http\FormRequest;

class UpdateStudentAssessmentScoreRequest extends FormRequest
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
            'assessment_id' => ['sometimes', 'exists:assessments,id'],
            'enrollment_id' => ['sometimes', 'exists:enrollments,id'],
            'score' => [
                'required',
                'numeric',
                'min:0',
                function (string $attribute, mixed $value, \Closure $fail) {
                    $assessmentId = $this->input('assessment_id');
                    if (!$assessmentId) {
                        $scoreParam = $this->route('student_assessment_score');
                        $scoreId = is_object($scoreParam) ? $scoreParam->id : $scoreParam;
                        if ($scoreId) {
                            $scoreModel = StudentAssessmentScore::find($scoreId);
                            if ($scoreModel) {
                                $assessmentId = $scoreModel->assessment_id;
                            }
                        }
                    }
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

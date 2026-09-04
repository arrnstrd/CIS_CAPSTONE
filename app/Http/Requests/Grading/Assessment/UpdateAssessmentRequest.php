<?php

namespace App\Http\Requests\Grading\Assessment;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAssessmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make the request.
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
            'teaching_assignment_id' => [
                'required',
                'exists:teaching_assignments,id',
            ],

            'assessment_category_id' => [
                'required',
                'exists:assessment_categories,id',
            ],

            'grading_period_id' => [
                'required',
                Rule::exists('grading_periods', 'id')->where(function ($query) {
                    $query->where('period_type', 'trimester')
                        ->where('sequence', '<=', 3);
                }),
            ],

            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'total_items' => [
                'required',
                'integer',
                'min:1',
            ],

            'assessment_date' => [
                'required',
                'date',
            ],

            'status' => [
                'required',
                'in:active,inactive',
            ],
        ];
    }
}
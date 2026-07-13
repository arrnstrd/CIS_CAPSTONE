<?php

namespace App\Http\Requests\Grading\GradingPeriod;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGradingPeriodRequest extends FormRequest
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
        $gradingPeriod = $this->route('grading_period');
        $gradingPeriodId = is_object($gradingPeriod) ? $gradingPeriod->id : $gradingPeriod;
        $gradingPeriodId = $gradingPeriodId ?? $this->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'sequence' => ['required', 'integer', 'min:1', Rule::unique('grading_periods', 'sequence')->ignore($gradingPeriodId)],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}

<?php

namespace App\Http\Requests\Grading\GradingPeriod;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGradingPeriodRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'sequence' => ['required', 'integer', 'min:1', Rule::unique('grading_periods', 'sequence')],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}

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
     * The grading system uses exactly three active terms:
     * Term 1, Term 2, and Term 3.
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'sequence' => [
                'required',
                'integer',
                'min:1',
                'max:3',
                Rule::unique('grading_periods', 'sequence'),
            ],

            'is_active' => [
                'sometimes',
                'boolean',
            ],
        ];
    }

    /**
     * Prepare the data before validation.
     *
     * New grading periods are always trimester terms.
     */
    protected function prepareForValidation(): void
    {
        $sequence = $this->input('sequence');

        if ($sequence !== null) {
            $this->merge([
                'name' => 'Term ' . $sequence,
            ]);
        }
    }
}
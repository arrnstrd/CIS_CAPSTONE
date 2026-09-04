<?php

namespace App\Http\Requests\SchoolAdmin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Subject;

class StoreSubjectRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'level' => Subject::normalizeLevel($this->input('level')),
        ]);
    }

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
            'code' => ['required', 'string', 'max:255', Rule::unique('subjects', 'code')],
            'name' => ['required', 'string', 'max:255'],
            'level' => ['required', 'string', Rule::in(array_keys(Subject::levelOptions()))],
        ];
    }
}

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
            'code' => ['nullable', 'string', 'max:255', Rule::unique('subjects', 'code')],
            'name' => ['nullable', 'string', 'max:255'],
            'names' => ['nullable', 'string', 'max:5000'],
            'level' => ['required', 'string', Rule::in(array_keys(Subject::levelOptions()))],
            'code_mode' => ['nullable', 'string', Rule::in(['auto', 'blank'])],
        ];
    }
}

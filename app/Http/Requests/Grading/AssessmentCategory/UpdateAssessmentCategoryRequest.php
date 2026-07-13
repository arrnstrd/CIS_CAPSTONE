<?php

namespace App\Http\Requests\Grading\AssessmentCategory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAssessmentCategoryRequest extends FormRequest
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
        $category = $this->route('assessment_category');
        $categoryId = is_object($category) ? $category->id : $category;
        $categoryId = $categoryId ?? $this->id;

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('assessment_categories', 'name')->ignore($categoryId)],
        ];
    }
}

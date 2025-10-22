<?php

namespace App\Http\Requests\Category;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class IndexCategoryRequest extends FormRequest
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
            'with_counts' => ['nullable', 'boolean'],
            'with_expenses' => ['nullable', 'boolean'],
            'sort_by' => ['nullable', 'string', 'in:name,created_at,expenses_count'],
            'sort_order' => ['nullable', 'string', 'in:asc,desc'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'with_counts.boolean' => 'With counts must be true or false.',
            'with_expenses.boolean' => 'With expenses must be true or false.',
            'sort_by.in' => 'Invalid sort field. Allowed: name, created_at, expenses_count.',
            'sort_order.in' => 'Sort order must be either asc or desc.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'with_counts' => $this->input('with_counts', true),
            'with_expenses' => $this->input('with_expenses', false),
            'sort_by' => $this->input('sort_by', 'name'),
            'sort_order' => $this->input('sort_order', 'asc'),
        ]);
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Invalid parameters for category listing.',
                'errors' => $validator->errors(),
            ], 422)
        );
    }
}

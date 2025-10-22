<?php

namespace App\Http\Requests\Budget;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class SearchBudgetRequest extends FormRequest
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
            'period' => ['nullable', 'string', 'in:weekly,monthly,yearly,custom'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'status' => ['nullable', 'string', 'in:active,current,over_budget,expired'],
            'is_active' => ['nullable', 'boolean'],
            'amount_min' => ['nullable', 'numeric', 'min:0'],
            'amount_max' => ['nullable', 'numeric', 'min:0', 'gte:amount_min'],
            'search' => ['nullable', 'string', 'max:255'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
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
            'period.in' => 'Invalid budget period. Allowed: weekly, monthly, yearly, custom.',
            'category_id.exists' => 'The selected category does not exist.',
            'status.in' => 'Invalid status. Allowed: active, current, over_budget, expired.',
            'is_active.boolean' => 'Active status must be true or false.',
            'amount_min.min' => 'Minimum amount cannot be negative.',
            'amount_max.min' => 'Maximum amount cannot be negative.',
            'amount_max.gte' => 'Maximum amount must be greater than or equal to minimum amount.',
            'search.max' => 'Search query cannot exceed 255 characters.',
            'per_page.max' => 'Items per page cannot exceed 100.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Sanitize search query
        if ($this->has('search')) {
            $this->merge([
                'search' => trim(strip_tags($this->input('search'))),
            ]);
        }

        // Set defaults
        $this->merge([
            'per_page' => $this->input('per_page', 15),
            'page' => $this->input('page', 1),
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
                'message' => 'Invalid search parameters for budgets.',
                'errors' => $validator->errors(),
            ], 422)
        );
    }
}

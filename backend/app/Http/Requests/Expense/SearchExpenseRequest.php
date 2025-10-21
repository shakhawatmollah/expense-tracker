<?php

namespace App\Http\Requests\Expense;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class SearchExpenseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'query' => ['nullable', 'string', 'max:255', 'min:1'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'start_date' => ['nullable', 'date', 'before_or_equal:end_date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'min_amount' => ['nullable', 'numeric', 'min:0'],
            'max_amount' => ['nullable', 'numeric', 'min:0', 'gte:min_amount'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
            'sort_by' => ['nullable', 'string', 'in:date,amount,description,created_at'],
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
            'query.string' => 'The search query must be a valid text string.',
            'query.max' => 'The search query cannot exceed 255 characters.',
            'query.min' => 'The search query must be at least 1 character.',
            'category_id.integer' => 'The category ID must be a valid number.',
            'category_id.exists' => 'The selected category does not exist.',
            'start_date.date' => 'The start date must be a valid date.',
            'start_date.before_or_equal' => 'The start date must be before or equal to the end date.',
            'end_date.date' => 'The end date must be a valid date.',
            'end_date.after_or_equal' => 'The end date must be after or equal to the start date.',
            'min_amount.numeric' => 'The minimum amount must be a valid number.',
            'min_amount.min' => 'The minimum amount cannot be negative.',
            'max_amount.numeric' => 'The maximum amount must be a valid number.',
            'max_amount.min' => 'The maximum amount cannot be negative.',
            'max_amount.gte' => 'The maximum amount must be greater than or equal to the minimum amount.',
            'per_page.integer' => 'Items per page must be a valid number.',
            'per_page.min' => 'Items per page must be at least 1.',
            'per_page.max' => 'Items per page cannot exceed 100.',
            'page.integer' => 'Page number must be a valid number.',
            'page.min' => 'Page number must be at least 1.',
            'sort_by.in' => 'Invalid sort field. Allowed: date, amount, description, created_at.',
            'sort_order.in' => 'Sort order must be either asc or desc.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'query' => 'search query',
            'category_id' => 'category',
            'start_date' => 'start date',
            'end_date' => 'end date',
            'min_amount' => 'minimum amount',
            'max_amount' => 'maximum amount',
            'per_page' => 'items per page',
            'sort_by' => 'sort field',
            'sort_order' => 'sort order',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Sanitize query string
        if ($this->has('query')) {
            $this->merge([
                'query' => trim(strip_tags($this->input('query')))
            ]);
        }

        // Set default values
        $this->merge([
            'per_page' => $this->input('per_page', 15),
            'page' => $this->input('page', 1),
            'sort_by' => $this->input('sort_by', 'date'),
            'sort_order' => $this->input('sort_order', 'desc'),
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
                'message' => 'Validation failed for search parameters.',
                'errors' => $validator->errors()
            ], 422)
        );
    }
}

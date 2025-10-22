<?php

namespace App\Http\Requests\Expense;

use Illuminate\Foundation\Http\FormRequest;

class StoreExpenseRequest extends FormRequest
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
            'description' => ['required', 'string', 'min:3', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
            'date' => ['required', 'date'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
        ];
    }

    /**
     * Get custom error messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'description.required' => 'Expense description is required',
            'description.min' => 'Description must be at least 3 characters',
            'description.max' => 'Description cannot exceed 255 characters',
            'amount.required' => 'Amount is required',
            'amount.numeric' => 'Amount must be a valid number',
            'amount.min' => 'Amount must be greater than 0',
            'amount.max' => 'Amount cannot exceed 999,999.99',
            'date.required' => 'Expense date is required',
            'date.date' => 'Please enter a valid date',
            'category_id.required' => 'Category is required',
            'category_id.exists' => 'Selected category does not exist',
        ];
    }
}

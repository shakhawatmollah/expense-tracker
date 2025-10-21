<?php

namespace App\Http\Requests\Expense;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Carbon\Carbon;

class DateRangeRequest extends FormRequest
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
            'start_date' => [
                'required',
                'date',
                'date_format:Y-m-d',
                'before_or_equal:end_date',
                'before_or_equal:today'
            ],
            'end_date' => [
                'required',
                'date',
                'date_format:Y-m-d',
                'after_or_equal:start_date',
                'before_or_equal:today'
            ],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'group_by' => ['nullable', 'string', 'in:day,week,month,year,category'],
            'include_summary' => ['nullable', 'boolean'],
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
            'start_date.required' => 'A start date is required.',
            'start_date.date' => 'The start date must be a valid date.',
            'start_date.date_format' => 'The start date must be in YYYY-MM-DD format.',
            'start_date.before_or_equal' => 'The start date must be before or equal to the end date.',
            'start_date.before_or_equal' => 'The start date cannot be in the future.',
            'end_date.required' => 'An end date is required.',
            'end_date.date' => 'The end date must be a valid date.',
            'end_date.date_format' => 'The end date must be in YYYY-MM-DD format.',
            'end_date.after_or_equal' => 'The end date must be after or equal to the start date.',
            'end_date.before_or_equal' => 'The end date cannot be in the future.',
            'category_id.integer' => 'The category ID must be a valid number.',
            'category_id.exists' => 'The selected category does not exist.',
            'group_by.in' => 'Invalid grouping option. Allowed: day, week, month, year, category.',
            'include_summary.boolean' => 'Include summary must be true or false.',
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
            'start_date' => 'start date',
            'end_date' => 'end date',
            'category_id' => 'category',
            'group_by' => 'grouping option',
            'include_summary' => 'summary inclusion',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Ensure dates are in correct format
        if ($this->has('start_date') && $this->input('start_date')) {
            try {
                $startDate = Carbon::parse($this->input('start_date'))->format('Y-m-d');
                $this->merge(['start_date' => $startDate]);
            } catch (\Exception $e) {
                // Let validation handle invalid dates
            }
        }

        if ($this->has('end_date') && $this->input('end_date')) {
            try {
                $endDate = Carbon::parse($this->input('end_date'))->format('Y-m-d');
                $this->merge(['end_date' => $endDate]);
            } catch (\Exception $e) {
                // Let validation handle invalid dates
            }
        }

        // Set defaults
        $this->merge([
            'include_summary' => $this->input('include_summary', true),
        ]);
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Check if date range is too large (e.g., more than 1 year)
            if ($this->start_date && $this->end_date) {
                $start = Carbon::parse($this->start_date);
                $end = Carbon::parse($this->end_date);
                
                if ($start->diffInDays($end) > 365) {
                    $validator->errors()->add(
                        'end_date',
                        'The date range cannot exceed 365 days.'
                    );
                }
            }
        });
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Invalid date range parameters.',
                'errors' => $validator->errors()
            ], 422)
        );
    }

    /**
     * Get the validated date range.
     *
     * @return array{start_date: string, end_date: string}
     */
    public function getDateRange(): array
    {
        return [
            'start_date' => $this->validated('start_date'),
            'end_date' => $this->validated('end_date'),
        ];
    }
}

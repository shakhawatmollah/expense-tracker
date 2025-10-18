<?php

namespace App\Http\Requests;

use App\Models\Budget;
use Illuminate\Foundation\Http\FormRequest;

class BudgetRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01|max:999999.99',
            'period' => 'required|in:' . implode(',', Budget::PERIODS),
            'start_date' => 'required_if:period,custom|nullable|date',
            'end_date' => 'required_if:period,custom|nullable|date|after:start_date',
            'category_id' => 'nullable|integer|exists:categories,id',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
            'alert_thresholds' => 'nullable|array',
            'alert_thresholds.warning' => 'nullable|numeric|min:0|max:100',
            'alert_thresholds.danger' => 'nullable|numeric|min:0|max:200',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Budget name is required.',
            'name.string' => 'Budget name must be a valid string.',
            'name.max' => 'Budget name cannot exceed 255 characters.',
            
            'amount.required' => 'Budget amount is required.',
            'amount.numeric' => 'Budget amount must be a valid number.',
            'amount.min' => 'Budget amount must be at least $0.01.',
            'amount.max' => 'Budget amount cannot exceed $999,999.99.',
            
            'period.required' => 'Budget period is required.',
            'period.in' => 'Please select a valid budget period.',
            
            'start_date.required_if' => 'Start date is required for custom periods.',
            'start_date.date' => 'Start date must be a valid date.',
            
            'end_date.required_if' => 'End date is required for custom periods.',
            'end_date.date' => 'End date must be a valid date.',
            'end_date.after' => 'End date must be after the start date.',
            
            'category_id.integer' => 'Category ID must be a valid integer.',
            'category_id.exists' => 'Selected category does not exist.',
            
            'description.string' => 'Description must be a valid string.',
            'description.max' => 'Description cannot exceed 1000 characters.',
            
            'is_active.boolean' => 'Active status must be true or false.',
            
            'alert_thresholds.array' => 'Alert thresholds must be an array.',
            'alert_thresholds.warning.numeric' => 'Warning threshold must be a number.',
            'alert_thresholds.warning.min' => 'Warning threshold cannot be negative.',
            'alert_thresholds.warning.max' => 'Warning threshold cannot exceed 100%.',
            'alert_thresholds.danger.numeric' => 'Danger threshold must be a number.',
            'alert_thresholds.danger.min' => 'Danger threshold cannot be negative.',
            'alert_thresholds.danger.max' => 'Danger threshold cannot exceed 200%.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Set default values
        if (!$this->has('is_active')) {
            $this->merge(['is_active' => true]);
        }

        // Clean up alert thresholds
        if ($this->has('alert_thresholds')) {
            $thresholds = $this->alert_thresholds;
            
            // Remove empty values
            $thresholds = array_filter($thresholds, function ($value) {
                return $value !== null && $value !== '';
            });
            
            // Set default values if not provided
            if (empty($thresholds)) {
                $thresholds = Budget::DEFAULT_ALERT_THRESHOLDS;
            } else {
                // Ensure we have both warning and danger thresholds
                if (!isset($thresholds['warning'])) {
                    $thresholds['warning'] = Budget::DEFAULT_ALERT_THRESHOLDS['warning'];
                }
                if (!isset($thresholds['danger'])) {
                    $thresholds['danger'] = Budget::DEFAULT_ALERT_THRESHOLDS['danger'];
                }
            }
            
            $this->merge(['alert_thresholds' => $thresholds]);
        }

        // For non-custom periods, clear start_date and end_date as they will be auto-calculated
        if ($this->period && $this->period !== Budget::PERIOD_CUSTOM) {
            $this->merge([
                'start_date' => null,
                'end_date' => null,
            ]);
        }
    }
}
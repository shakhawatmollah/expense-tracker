<?php

namespace App\Exceptions;

class BudgetDatabaseException extends BudgetException
{
    /**
     * Create a new budget database exception
     *
     * @param string $operation The database operation that failed (e.g., 'budget creation', 'budget update')
     * @param string $details Additional error details
     * @param array $context Additional context data
     */
    public function __construct(
        string $operation,
        string $details = '',
        array $context = []
    ) {
        $message = "Database error during {$operation}";
        if ($details) {
            $message .= ": {$details}";
        }

        parent::__construct(
            $message,
            'Unable to process your budget. Please try again later.',
            500,
            array_merge(['operation' => $operation, 'details' => $details], $context)
        );
    }
}

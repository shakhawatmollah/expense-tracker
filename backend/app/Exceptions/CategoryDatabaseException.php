<?php

namespace App\Exceptions;

class CategoryDatabaseException extends CategoryException
{
    /**
     * Create a new category database exception
     *
     * @param string $operation The database operation that failed (e.g., 'category creation', 'category update')
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
            'Unable to process your category. Please try again later.',
            500,
            array_merge(['operation' => $operation, 'details' => $details], $context)
        );
    }
}

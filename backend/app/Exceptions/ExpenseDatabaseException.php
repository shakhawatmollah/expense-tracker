<?php

namespace App\Exceptions;

/**
 * Exception thrown when database operations fail for expenses
 */
class ExpenseDatabaseException extends ExpenseException
{
    protected $statusCode = 500;

    /**
     * Create a new expense database exception
     *
     * @param string $operation Database operation that failed
     * @param string $details Error details
     * @param array $context Additional context
     */
    public function __construct(string $operation, string $details = '', array $context = [])
    {
        $message = "Database error during {$operation}: {$details}";
        $userMessage = "Unable to process your expense. Please try again later.";
        
        parent::__construct($message, $userMessage, 500, $context);
    }
}

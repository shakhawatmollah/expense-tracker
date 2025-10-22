<?php

namespace App\Exceptions;

/**
 * Exception thrown when an expense is not found
 */
class ExpenseNotFoundException extends ExpenseException
{
    protected $statusCode = 404;

    /**
     * Create a new expense not found exception
     *
     * @param int|string $identifier Expense ID or identifier
     * @param array $context Additional context
     */
    public function __construct($identifier = null, array $context = [])
    {
        if ($identifier === null) {
            $message = 'Expense not found or access denied';
            $userMessage = 'The requested expense could not be found.';
        } else {
            $message = "Expense not found with identifier: {$identifier}";
            $userMessage = 'The requested expense could not be found.';
        }

        parent::__construct($message, $userMessage, 404, $context);
    }
}

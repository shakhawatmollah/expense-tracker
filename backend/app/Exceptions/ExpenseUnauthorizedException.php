<?php

namespace App\Exceptions;

/**
 * Exception thrown when user is not authorized to access an expense
 */
class ExpenseUnauthorizedException extends ExpenseException
{
    protected $statusCode = 403;

    /**
     * Create a new expense unauthorized exception
     *
     * @param int $expenseId Expense ID
     * @param int $userId User ID
     * @param array $context Additional context
     */
    public function __construct(int $expenseId, int $userId, array $context = [])
    {
        $message = "User {$userId} not authorized to access expense {$expenseId}";
        $userMessage = 'You are not authorized to access this expense.';

        parent::__construct($message, $userMessage, 403, $context);
    }
}

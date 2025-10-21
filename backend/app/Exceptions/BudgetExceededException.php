<?php

namespace App\Exceptions;

class BudgetExceededException extends BudgetException
{
    protected $statusCode = 422;

    public function __construct(float $budgetAmount, float $totalExpenses, array $context = [])
    {
        $message = "Budget exceeded: Budget {$budgetAmount}, Expenses {$totalExpenses}";
        $userMessage = "This expense would exceed your budget limit.";
        
        parent::__construct($message, $userMessage, 422, array_merge($context, [
            'budget_amount' => $budgetAmount,
            'total_expenses' => $totalExpenses,
        ]));
    }
}

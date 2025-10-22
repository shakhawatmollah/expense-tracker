<?php

namespace App\Exceptions;

class BudgetNotFoundException extends BudgetException
{
    protected $statusCode = 404;

    public function __construct($identifier = null, array $context = [])
    {
        if ($identifier === null) {
            $message = 'Budget not found or access denied';
            $userMessage = 'The requested budget could not be found.';
        } else {
            $message = "Budget not found with identifier: {$identifier}";
            $userMessage = 'The requested budget could not be found.';
        }

        parent::__construct($message, $userMessage, 404, $context);
    }
}

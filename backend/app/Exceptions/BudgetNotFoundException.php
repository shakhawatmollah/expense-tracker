<?php

namespace App\Exceptions;

use Exception;

class BudgetNotFoundException extends Exception
{
    public function __construct(string $message = 'Budget not found or access denied')
    {
        parent::__construct($message);
    }
}
<?php

namespace App\Exceptions;

use Exception;

class ExpenseNotFoundException extends Exception
{
    public function __construct(string $message = 'Expense not found or access denied')
    {
        parent::__construct($message);
    }
}
<?php

namespace App\Exceptions;

use Exception;

class CategoryNotFoundException extends Exception
{
    public function __construct(string $message = 'Category not found or access denied')
    {
        parent::__construct($message);
    }
}
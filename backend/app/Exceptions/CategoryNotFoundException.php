<?php

namespace App\Exceptions;

class CategoryNotFoundException extends CategoryException
{
    protected $statusCode = 404;

    public function __construct($identifier = null, array $context = [])
    {
        if ($identifier === null) {
            $message = "Category not found or access denied";
            $userMessage = "The requested category could not be found.";
        } else {
            $message = "Category not found with identifier: {$identifier}";
            $userMessage = "The requested category could not be found.";
        }
        
        parent::__construct($message, $userMessage, 404, $context);
    }
}
<?php

namespace App\Exceptions;

class CategoryValidationException extends CategoryException
{
    protected $statusCode = 422;
    protected $errors = [];

    public function __construct(string $message, array $errors = [], array $context = [])
    {
        $userMessage = 'The category data is invalid. Please check your input.';
        $this->errors = $errors;

        parent::__construct($message, $userMessage, 422, $context);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'errors' => $this->getErrors(),
        ]);
    }
}

<?php

namespace App\Exceptions;

/**
 * Exception thrown when expense validation fails
 */
class ExpenseValidationException extends ExpenseException
{
    protected $statusCode = 422;

    /**
     * Validation errors
     *
     * @var array
     */
    protected $errors = [];

    /**
     * Create a new expense validation exception
     *
     * @param string $message Error message
     * @param array $errors Validation errors
     * @param array $context Additional context
     */
    public function __construct(string $message, array $errors = [], array $context = [])
    {
        $userMessage = 'The expense data is invalid. Please check your input.';

        $this->errors = $errors;

        parent::__construct($message, $userMessage, 422, $context);
    }

    /**
     * Get validation errors
     *
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Convert exception to array for JSON response
     *
     * @return array
     */
    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'errors' => $this->getErrors(),
        ]);
    }
}

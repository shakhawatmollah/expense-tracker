<?php

namespace App\Exceptions;

use Exception;

/**
 * Base exception for all expense-related errors
 */
class ExpenseException extends Exception
{
    /**
     * HTTP status code for the exception
     *
     * @var int
     */
    protected $statusCode = 500;

    /**
     * User-friendly error message
     *
     * @var string
     */
    protected $userMessage;

    /**
     * Additional context data
     *
     * @var array
     */
    protected $context = [];

    /**
     * Create a new expense exception instance
     *
     * @param string $message Internal error message
     * @param string|null $userMessage User-friendly message
     * @param int|null $statusCode HTTP status code
     * @param array $context Additional context
     * @param \Throwable|null $previous Previous exception
     */
    public function __construct(
        string $message = '',
        ?string $userMessage = null,
        ?int $statusCode = null,
        array $context = [],
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, 0, $previous);

        $this->userMessage = $userMessage ?? $message;
        $this->statusCode = $statusCode ?? $this->statusCode;
        $this->context = $context;
    }

    /**
     * Get HTTP status code
     *
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Get user-friendly message
     *
     * @return string
     */
    public function getUserMessage(): string
    {
        return $this->userMessage;
    }

    /**
     * Get additional context
     *
     * @return array
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * Convert exception to array for JSON response
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'message' => $this->getUserMessage(),
            'code' => $this->getCode(),
            'status' => $this->getStatusCode(),
        ];
    }
}

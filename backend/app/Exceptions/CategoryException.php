<?php

namespace App\Exceptions;

use Exception;

/**
 * Base exception for all category-related errors
 */
class CategoryException extends Exception
{
    protected $statusCode = 500;
    protected $userMessage;
    protected $context = [];

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

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getUserMessage(): string
    {
        return $this->userMessage;
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public function toArray(): array
    {
        return [
            'message' => $this->getUserMessage(),
            'code' => $this->getCode(),
            'status' => $this->getStatusCode(),
        ];
    }
}

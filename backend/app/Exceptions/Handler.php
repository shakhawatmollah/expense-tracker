<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        // Handle custom expense exceptions
        $this->renderable(function (ExpenseException $e, Request $request) {
            if ($request->expectsJson()) {
                Log::warning('Expense Exception', [
                    'exception' => get_class($e),
                    'message' => $e->getMessage(),
                    'context' => $e->getContext(),
                    'user_id' => $request->user()?->id,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => $e->getUserMessage(),
                    'errors' => method_exists($e, 'getErrors') ? $e->getErrors() : null,
                ], $e->getStatusCode());
            }
        });

        // Handle custom budget exceptions
        $this->renderable(function (BudgetException $e, Request $request) {
            if ($request->expectsJson()) {
                Log::warning('Budget Exception', [
                    'exception' => get_class($e),
                    'message' => $e->getMessage(),
                    'context' => $e->getContext(),
                    'user_id' => $request->user()?->id,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => $e->getUserMessage(),
                    'errors' => method_exists($e, 'getErrors') ? $e->getErrors() : null,
                ], $e->getStatusCode());
            }
        });

        // Handle custom category exceptions
        $this->renderable(function (CategoryException $e, Request $request) {
            if ($request->expectsJson()) {
                Log::warning('Category Exception', [
                    'exception' => get_class($e),
                    'message' => $e->getMessage(),
                    'context' => $e->getContext(),
                    'user_id' => $request->user()?->id,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => $e->getUserMessage(),
                    'errors' => method_exists($e, 'getErrors') ? $e->getErrors() : null,
                ], $e->getStatusCode());
            }
        });

        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $exception)
    {
        // Handle API requests with JSON responses
        if ($request->expectsJson()) {
            return $this->handleApiException($request, $exception);
        }

        return parent::render($request, $exception);
    }

    /**
     * Handle API exceptions and return consistent JSON responses
     */
    protected function handleApiException(Request $request, Throwable $exception): JsonResponse
    {
        // Log the exception for debugging
        if (! $exception instanceof ValidationException) {
            Log::error('API Exception', [
                'exception' => get_class($exception),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'user_id' => $request->user()?->id,
            ]);
        }

        // Handle specific exception types
        return match (true) {
            $exception instanceof ValidationException => $this->handleValidationException($exception),
            $exception instanceof AuthenticationException => $this->handleAuthenticationException($exception),
            $exception instanceof AuthorizationException => $this->handleAuthorizationException($exception),
            $exception instanceof ModelNotFoundException => $this->handleModelNotFoundException($exception),
            $exception instanceof NotFoundHttpException => $this->handleNotFoundHttpException($exception),
            $exception instanceof MethodNotAllowedHttpException => $this->handleMethodNotAllowedHttpException($exception),
            $exception instanceof HttpException => $this->handleHttpException($exception),
            default => $this->handleGenericException($exception)
        };
    }

    /**
     * Handle validation exceptions
     */
    protected function handleValidationException(ValidationException $exception): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $exception->errors(),
        ], 422);
    }

    /**
     * Handle authentication exceptions
     */
    protected function handleAuthenticationException(AuthenticationException $exception): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Authentication required',
            'error' => 'Please log in to access this resource',
        ], 401);
    }

    /**
     * Handle authorization exceptions
     */
    protected function handleAuthorizationException(AuthorizationException $exception): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Access denied',
            'error' => 'You do not have permission to perform this action',
        ], 403);
    }

    /**
     * Handle model not found exceptions
     */
    protected function handleModelNotFoundException(ModelNotFoundException $exception): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Resource not found',
            'error' => 'The requested resource could not be found',
        ], 404);
    }

    /**
     * Handle not found HTTP exceptions
     */
    protected function handleNotFoundHttpException(NotFoundHttpException $exception): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Endpoint not found',
            'error' => 'The requested endpoint does not exist',
        ], 404);
    }

    /**
     * Handle method not allowed HTTP exceptions
     */
    protected function handleMethodNotAllowedHttpException(MethodNotAllowedHttpException $exception): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Method not allowed',
            'error' => 'The HTTP method is not supported for this endpoint',
        ], 405);
    }

    /**
     * Handle HTTP exceptions
     */
    protected function handleHttpException(HttpException $exception): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'HTTP error',
            'error' => $exception->getMessage() ?: 'An HTTP error occurred',
        ], $exception->getStatusCode());
    }

    /**
     * Handle generic exceptions
     */
    protected function handleGenericException(Throwable $exception): JsonResponse
    {
        // Don't expose internal errors in production
        if (config('app.debug')) {
            return response()->json([
                'success' => false,
                'message' => 'Server error',
                'error' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
            ], 500);
        }

        return response()->json([
            'success' => false,
            'message' => 'Internal server error',
            'error' => 'An unexpected error occurred. Please try again later.',
        ], 500);
    }
}

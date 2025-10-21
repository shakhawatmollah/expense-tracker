<?php

namespace App\Http\Helpers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Standardized API Response Helper
 * 
 * Provides consistent response structure across all API endpoints:
 * - success: boolean
 * - message: string (user-friendly)
 * - data: mixed (resource data)
 * - meta: array (pagination, filters, etc.)
 * - errors: array (validation/error details)
 */
class ApiResponse
{
    /**
     * Success response with data
     *
     * @param mixed $data Response data (Model, Collection, Resource, etc.)
     * @param string|null $message Success message
     * @param array $meta Additional metadata
     * @param int $statusCode HTTP status code
     * @return JsonResponse
     */
    public static function success(
        mixed $data = null,
        ?string $message = null,
        array $meta = [],
        int $statusCode = 200
    ): JsonResponse {
        $response = [
            'success' => true,
        ];

        if ($message !== null) {
            $response['message'] = $message;
        }

        // Handle different data types
        if ($data !== null) {
            $response['data'] = self::formatData($data);
        }

        // Add metadata if provided
        if (!empty($meta)) {
            $response['meta'] = $meta;
        }

        // Add pagination if data is paginated
        if ($data instanceof LengthAwarePaginator) {
            $response['meta'] = array_merge($meta, self::getPaginationMeta($data));
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Created response (201)
     *
     * @param mixed $data Created resource
     * @param string|null $message Success message
     * @param array $meta Additional metadata
     * @return JsonResponse
     */
    public static function created(
        mixed $data,
        ?string $message = 'Resource created successfully',
        array $meta = []
    ): JsonResponse {
        return self::success($data, $message, $meta, 201);
    }

    /**
     * No content response (204)
     *
     * @return JsonResponse
     */
    public static function noContent(): JsonResponse
    {
        return response()->json(null, 204);
    }

    /**
     * Error response
     *
     * @param string $message Error message
     * @param array $errors Detailed error information
     * @param int $statusCode HTTP status code
     * @param array $meta Additional metadata
     * @return JsonResponse
     */
    public static function error(
        string $message,
        array $errors = [],
        int $statusCode = 400,
        array $meta = []
    ): JsonResponse {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if (!empty($errors)) {
            $response['errors'] = $errors;
        }

        if (!empty($meta)) {
            $response['meta'] = $meta;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Validation error response (422)
     *
     * @param array $errors Validation errors
     * @param string|null $message Error message
     * @return JsonResponse
     */
    public static function validationError(
        array $errors,
        ?string $message = 'Validation failed'
    ): JsonResponse {
        return self::error($message, $errors, 422);
    }

    /**
     * Not found response (404)
     *
     * @param string|null $message Error message
     * @param string|null $resource Resource type that was not found
     * @return JsonResponse
     */
    public static function notFound(
        ?string $message = 'Resource not found',
        ?string $resource = null
    ): JsonResponse {
        $meta = [];
        if ($resource) {
            $meta['resource'] = $resource;
        }

        return self::error($message, [], 404, $meta);
    }

    /**
     * Unauthorized response (401)
     *
     * @param string|null $message Error message
     * @return JsonResponse
     */
    public static function unauthorized(
        ?string $message = 'Unauthorized access'
    ): JsonResponse {
        return self::error($message, [], 401);
    }

    /**
     * Forbidden response (403)
     *
     * @param string|null $message Error message
     * @return JsonResponse
     */
    public static function forbidden(
        ?string $message = 'Access forbidden'
    ): JsonResponse {
        return self::error($message, [], 403);
    }

    /**
     * Server error response (500)
     *
     * @param string|null $message Error message
     * @param array $errors Error details (only in debug mode)
     * @return JsonResponse
     */
    public static function serverError(
        ?string $message = 'Internal server error',
        array $errors = []
    ): JsonResponse {
        // Only include detailed errors in non-production
        $detailedErrors = app()->environment('production') ? [] : $errors;
        
        return self::error($message, $detailedErrors, 500);
    }

    /**
     * Conflict response (409)
     *
     * @param string $message Conflict message
     * @param array $errors Conflict details
     * @return JsonResponse
     */
    public static function conflict(
        string $message = 'Resource conflict',
        array $errors = []
    ): JsonResponse {
        return self::error($message, $errors, 409);
    }

    /**
     * Too Many Requests response (429)
     *
     * @param string|null $message Rate limit message
     * @param int|null $retryAfter Seconds until retry is allowed
     * @return JsonResponse
     */
    public static function tooManyRequests(
        ?string $message = 'Too many requests',
        ?int $retryAfter = null
    ): JsonResponse {
        $meta = [];
        if ($retryAfter) {
            $meta['retry_after'] = $retryAfter;
        }

        $response = self::error($message, [], 429, $meta);

        if ($retryAfter) {
            $response->header('Retry-After', $retryAfter);
        }

        return $response;
    }

    /**
     * Format data based on type
     *
     * @param mixed $data
     * @return mixed
     */
    private static function formatData(mixed $data): mixed
    {
        // JsonResource handles its own formatting
        if ($data instanceof JsonResource || $data instanceof ResourceCollection) {
            return $data;
        }

        // Paginator is handled separately
        if ($data instanceof LengthAwarePaginator) {
            return $data->items();
        }

        // Return as-is for other types
        return $data;
    }

    /**
     * Extract pagination metadata
     *
     * @param LengthAwarePaginator $paginator
     * @return array
     */
    private static function getPaginationMeta(LengthAwarePaginator $paginator): array
    {
        return [
            'pagination' => [
                'total' => $paginator->total(),
                'count' => $paginator->count(),
                'per_page' => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'total_pages' => $paginator->lastPage(),
                'has_more_pages' => $paginator->hasMorePages(),
            ]
        ];
    }

    /**
     * Success response with collection
     *
     * @param iterable $collection Collection of items
     * @param string|null $message Success message
     * @param array $meta Additional metadata
     * @return JsonResponse
     */
    public static function collection(
        iterable $collection,
        ?string $message = null,
        array $meta = []
    ): JsonResponse {
        $items = is_array($collection) ? $collection : iterator_to_array($collection);
        
        $meta['count'] = count($items);

        return self::success($items, $message, $meta);
    }

    /**
     * Success response with message only (no data)
     *
     * @param string $message Success message
     * @param array $meta Additional metadata
     * @return JsonResponse
     */
    public static function message(
        string $message,
        array $meta = []
    ): JsonResponse {
        return self::success(null, $message, $meta);
    }
}

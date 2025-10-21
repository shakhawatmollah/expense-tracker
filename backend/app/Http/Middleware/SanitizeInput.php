<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Input Sanitization Middleware
 * 
 * Automatically sanitizes all incoming request data to prevent XSS attacks
 * and ensure data integrity. Applies recursive sanitization to all input types.
 */
class SanitizeInput
{
    /**
     * Fields that should not be sanitized (passwords, tokens, etc.)
     *
     * @var array
     */
    protected array $except = [
        'password',
        'password_confirmation',
        'current_password',
        'token',
        'api_token',
        'access_token',
        'refresh_token',
        '_token',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only sanitize for specific methods
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH'])) {
            $this->sanitizeInput($request);
        }

        return $next($request);
    }

    /**
     * Sanitize request input
     *
     * @param Request $request
     * @return void
     */
    protected function sanitizeInput(Request $request): void
    {
        $input = $request->all();
        
        $sanitized = $this->sanitizeData($input);
        
        $request->merge($sanitized);
    }

    /**
     * Recursively sanitize data
     *
     * @param mixed $data
     * @param string|null $key
     * @return mixed
     */
    protected function sanitizeData(mixed $data, ?string $key = null): mixed
    {
        // Skip sanitization for excluded fields
        if ($key && in_array($key, $this->except)) {
            return $data;
        }

        // Handle different data types
        if (is_array($data)) {
            return $this->sanitizeArray($data);
        }

        if (is_string($data)) {
            return $this->sanitizeString($data);
        }

        // Return other types as-is (int, float, bool, null, etc.)
        return $data;
    }

    /**
     * Sanitize array recursively
     *
     * @param array $array
     * @return array
     */
    protected function sanitizeArray(array $array): array
    {
        $sanitized = [];

        foreach ($array as $key => $value) {
            $sanitized[$key] = $this->sanitizeData($value, $key);
        }

        return $sanitized;
    }

    /**
     * Sanitize string value
     *
     * @param string $value
     * @return string
     */
    protected function sanitizeString(string $value): string
    {
        // Remove null bytes
        $value = str_replace("\0", '', $value);

        // Strip HTML and PHP tags
        $value = strip_tags($value);

        // Remove potential script injections
        $value = $this->removeScriptInjections($value);

        // Trim whitespace
        $value = trim($value);

        // Convert special characters to HTML entities (additional protection)
        $value = $this->escapeSpecialChars($value);

        return $value;
    }

    /**
     * Remove common script injection patterns
     *
     * @param string $value
     * @return string
     */
    protected function removeScriptInjections(string $value): string
    {
        // Remove javascript: protocol
        $value = preg_replace('/javascript:/i', '', $value);

        // Remove data: protocol
        $value = preg_replace('/data:text\/html/i', '', $value);

        // Remove vbscript: protocol
        $value = preg_replace('/vbscript:/i', '', $value);

        // Remove on* event handlers (onclick, onload, etc.)
        $value = preg_replace('/on\w+\s*=/i', '', $value);

        // Remove <script> tags (in case strip_tags didn't catch encoded versions)
        $value = preg_replace('/<script[^>]*>.*?<\/script>/is', '', $value);

        // Remove <iframe> tags
        $value = preg_replace('/<iframe[^>]*>.*?<\/iframe>/is', '', $value);

        // Remove <object> tags
        $value = preg_replace('/<object[^>]*>.*?<\/object>/is', '', $value);

        // Remove <embed> tags
        $value = preg_replace('/<embed[^>]*>/is', '', $value);

        return $value;
    }

    /**
     * Escape special characters
     *
     * @param string $value
     * @return string
     */
    protected function escapeSpecialChars(string $value): string
    {
        // Convert special characters to HTML entities
        // This prevents XSS while preserving the display
        return htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8', false);
    }

    /**
     * Check if field should skip sanitization
     *
     * @param string $field
     * @return bool
     */
    protected function shouldSkip(string $field): bool
    {
        return in_array($field, $this->except);
    }
}

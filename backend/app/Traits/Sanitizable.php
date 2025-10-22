<?php

namespace App\Traits;

/**
 * Sanitizable Trait
 *
 * Provides sanitization methods for models and services to clean data
 * before storage or processing.
 */
trait Sanitizable
{
    /**
     * Sanitize all string attributes before setting them
     *
     * @param array $attributes
     * @return array
     */
    protected function sanitizeAttributes(array $attributes): array
    {
        $sanitized = [];

        foreach ($attributes as $key => $value) {
            if ($this->shouldSanitizeField($key)) {
                $sanitized[$key] = $this->sanitizeValue($value);
            } else {
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }

    /**
     * Sanitize a single value
     *
     * @param mixed $value
     * @return mixed
     */
    protected function sanitizeValue(mixed $value): mixed
    {
        if (is_array($value)) {
            return $this->sanitizeAttributes($value);
        }

        if (is_string($value)) {
            return $this->sanitizeString($value);
        }

        return $value;
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

        // Trim whitespace
        $value = trim($value);

        // Remove leading/trailing whitespace and normalize spaces
        $value = preg_replace('/\s+/', ' ', $value);

        // Remove control characters except newlines and tabs
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value);

        return $value;
    }

    /**
     * Sanitize HTML content (for rich text fields)
     *
     * @param string $html
     * @return string
     */
    protected function sanitizeHtml(string $html): string
    {
        // Allow only safe HTML tags
        $allowedTags = '<p><br><strong><em><u><ul><ol><li><a><h1><h2><h3><h4><h5><h6>';

        $html = strip_tags($html, $allowedTags);

        // Remove dangerous attributes
        $html = preg_replace('/<([a-z][a-z0-9]*)[^>]*?(on\w+\s*=)[^>]*?>/is', '<$1>', $html);

        // Remove javascript: protocol
        $html = preg_replace('/href\s*=\s*["\']?\s*javascript:/i', 'href="#"', $html);

        return $html;
    }

    /**
     * Sanitize email address
     *
     * @param string $email
     * @return string
     */
    protected function sanitizeEmail(string $email): string
    {
        // Remove all illegal characters from email
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);

        // Convert to lowercase
        $email = strtolower(trim($email));

        return $email;
    }

    /**
     * Sanitize URL
     *
     * @param string $url
     * @return string
     */
    protected function sanitizeUrl(string $url): string
    {
        // Remove all illegal characters from a url
        $url = filter_var($url, FILTER_SANITIZE_URL);

        return trim($url);
    }

    /**
     * Sanitize filename
     *
     * @param string $filename
     * @return string
     */
    protected function sanitizeFilename(string $filename): string
    {
        // Remove any path traversal attempts
        $filename = basename($filename);

        // Remove special characters
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);

        // Remove multiple consecutive underscores
        $filename = preg_replace('/_+/', '_', $filename);

        return trim($filename, '_');
    }

    /**
     * Sanitize phone number
     *
     * @param string $phone
     * @return string
     */
    protected function sanitizePhone(string $phone): string
    {
        // Remove all non-numeric characters except + at the start
        $phone = preg_replace('/[^0-9+]/', '', $phone);

        // Ensure + is only at the start
        if (str_starts_with($phone, '+')) {
            $phone = '+' . preg_replace('/[^0-9]/', '', substr($phone, 1));
        }

        return $phone;
    }

    /**
     * Sanitize numeric value
     *
     * @param mixed $value
     * @return float|int|null
     */
    protected function sanitizeNumeric(mixed $value): float|int|null
    {
        if ($value === null || $value === '') {
            return null;
        }

        // Remove any non-numeric characters except decimal point and minus
        $value = preg_replace('/[^0-9.-]/', '', (string)$value);

        // Check if it's a decimal number
        if (str_contains($value, '.')) {
            return (float)$value;
        }

        return (int)$value;
    }

    /**
     * Check if field should be sanitized
     *
     * @param string $field
     * @return bool
     */
    protected function shouldSanitizeField(string $field): bool
    {
        // Don't sanitize password fields
        $skipFields = ['password', 'password_confirmation', 'token', 'api_token'];

        return ! in_array($field, $skipFields);
    }

    /**
     * Escape output for safe display
     *
     * @param string $value
     * @return string
     */
    protected function escapeForDisplay(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * Remove XSS attempts from string
     *
     * @param string $value
     * @return string
     */
    protected function removeXss(string $value): string
    {
        // Remove script tags
        $value = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $value);

        // Remove javascript: protocol
        $value = preg_replace('/javascript:/i', '', $value);

        // Remove on* event handlers
        $value = preg_replace('/on\w+\s*=/i', '', $value);

        // Remove data: protocol
        $value = preg_replace('/data:text\/html/i', '', $value);

        return $value;
    }
}

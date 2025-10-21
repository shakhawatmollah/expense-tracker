<?php

namespace App\Helpers;

/**
 * Input Sanitization Helper
 * 
 * Provides static methods for sanitizing various types of input data
 * to prevent XSS, SQL injection, and other security vulnerabilities.
 */
class Sanitizer
{
    /**
     * Sanitize a string value
     *
     * @param string|null $value
     * @param bool $allowHtml
     * @return string|null
     */
    public static function string(?string $value, bool $allowHtml = false): ?string
    {
        if ($value === null) {
            return null;
        }

        // Remove null bytes
        $value = str_replace("\0", '', $value);

        if (!$allowHtml) {
            // Strip all HTML tags
            $value = strip_tags($value);
        } else {
            // Sanitize HTML (allow safe tags only)
            $value = self::html($value);
        }

        // Remove potential script injections
        $value = self::removeScriptInjections($value);

        // Trim whitespace
        $value = trim($value);

        // Normalize whitespace
        $value = preg_replace('/\s+/', ' ', $value);

        return $value;
    }

    /**
     * Sanitize HTML content (allow safe tags)
     *
     * @param string|null $html
     * @return string|null
     */
    public static function html(?string $html): ?string
    {
        if ($html === null) {
            return null;
        }

        // Define allowed tags
        $allowedTags = '<p><br><strong><em><u><ul><ol><li><a><h1><h2><h3><h4><h5><h6><blockquote><code><pre>';
        
        // Strip unwanted tags
        $html = strip_tags($html, $allowedTags);

        // Remove dangerous attributes (on* events, javascript: protocol)
        $html = preg_replace('/<([a-z][a-z0-9]*)[^>]*?(on\w+\s*=)[^>]*?>/is', '<$1>', $html);
        $html = preg_replace('/href\s*=\s*["\']?\s*javascript:/i', 'href="#"', $html);
        $html = preg_replace('/src\s*=\s*["\']?\s*javascript:/i', 'src=""', $html);
        
        // Remove style attributes (can contain javascript)
        $html = preg_replace('/<([a-z][a-z0-9]*)[^>]*?(style\s*=)[^>]*?>/is', '<$1>', $html);

        return trim($html);
    }

    /**
     * Sanitize email address
     *
     * @param string|null $email
     * @return string|null
     */
    public static function email(?string $email): ?string
    {
        if ($email === null) {
            return null;
        }

        // Remove all illegal characters from email
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        
        // Convert to lowercase
        $email = strtolower(trim($email));
        
        return $email ?: null;
    }

    /**
     * Sanitize URL
     *
     * @param string|null $url
     * @return string|null
     */
    public static function url(?string $url): ?string
    {
        if ($url === null) {
            return null;
        }

        // Remove all illegal characters from a url
        $url = filter_var($url, FILTER_SANITIZE_URL);
        
        // Ensure only http(s) protocol
        if (!preg_match('/^https?:\/\//i', $url)) {
            return null;
        }

        return trim($url) ?: null;
    }

    /**
     * Sanitize filename
     *
     * @param string|null $filename
     * @return string|null
     */
    public static function filename(?string $filename): ?string
    {
        if ($filename === null) {
            return null;
        }

        // Remove any path traversal attempts
        $filename = basename($filename);
        
        // Remove special characters except . - _
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
        
        // Remove multiple consecutive underscores/dots
        $filename = preg_replace('/[_\.]+/', '_', $filename);
        
        // Trim dots and underscores from edges
        $filename = trim($filename, '_.');

        return $filename ?: null;
    }

    /**
     * Sanitize phone number
     *
     * @param string|null $phone
     * @return string|null
     */
    public static function phone(?string $phone): ?string
    {
        if ($phone === null) {
            return null;
        }

        // Remove all non-numeric characters except + at the start
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        
        // Ensure + is only at the start
        if (str_starts_with($phone, '+')) {
            $phone = '+' . preg_replace('/[^0-9]/', '', substr($phone, 1));
        } else {
            $phone = preg_replace('/[^0-9]/', '', $phone);
        }

        return $phone ?: null;
    }

    /**
     * Sanitize numeric value
     *
     * @param mixed $value
     * @return float|int|null
     */
    public static function numeric(mixed $value): float|int|null
    {
        if ($value === null || $value === '') {
            return null;
        }

        // Convert to string for processing
        $value = (string)$value;

        // Remove any non-numeric characters except decimal point and minus
        $value = preg_replace('/[^0-9.-]/', '', $value);

        // Handle empty string after sanitization
        if ($value === '' || $value === '-') {
            return null;
        }

        // Check if it's a decimal number
        if (str_contains($value, '.')) {
            return (float)$value;
        }

        return (int)$value;
    }

    /**
     * Sanitize integer value
     *
     * @param mixed $value
     * @return int|null
     */
    public static function integer(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        $sanitized = self::numeric($value);
        
        return $sanitized !== null ? (int)$sanitized : null;
    }

    /**
     * Sanitize float value
     *
     * @param mixed $value
     * @param int $decimals
     * @return float|null
     */
    public static function float(mixed $value, int $decimals = 2): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        $sanitized = self::numeric($value);
        
        return $sanitized !== null ? round((float)$sanitized, $decimals) : null;
    }

    /**
     * Sanitize boolean value
     *
     * @param mixed $value
     * @return bool
     */
    public static function boolean(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        // Convert string representations
        if (is_string($value)) {
            $value = strtolower(trim($value));
            return in_array($value, ['1', 'true', 'yes', 'on'], true);
        }

        return (bool)$value;
    }

    /**
     * Sanitize array recursively
     *
     * @param array|null $array
     * @param bool $allowHtml
     * @return array|null
     */
    public static function array(?array $array, bool $allowHtml = false): ?array
    {
        if ($array === null) {
            return null;
        }

        $sanitized = [];

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $sanitized[$key] = self::array($value, $allowHtml);
            } elseif (is_string($value)) {
                $sanitized[$key] = self::string($value, $allowHtml);
            } else {
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }

    /**
     * Escape output for safe display in HTML
     *
     * @param string|null $value
     * @return string|null
     */
    public static function escape(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * Remove script injection patterns
     *
     * @param string $value
     * @return string
     */
    protected static function removeScriptInjections(string $value): string
    {
        // Remove javascript: protocol
        $value = preg_replace('/javascript:/i', '', $value);

        // Remove data: protocol (can be used for XSS)
        $value = preg_replace('/data:text\/html/i', '', $value);

        // Remove vbscript: protocol
        $value = preg_replace('/vbscript:/i', '', $value);

        // Remove on* event handlers
        $value = preg_replace('/on\w+\s*=/i', '', $value);

        // Remove <script> tags (catch encoded versions)
        $value = preg_replace('/<script[^>]*>.*?<\/script>/is', '', $value);

        // Remove <iframe> tags
        $value = preg_replace('/<iframe[^>]*>.*?<\/iframe>/is', '', $value);

        // Remove <object> and <embed> tags
        $value = preg_replace('/<(object|embed)[^>]*>.*?<\/\1>/is', '', $value);

        return $value;
    }

    /**
     * Sanitize JSON string
     *
     * @param string|null $json
     * @return string|null
     */
    public static function json(?string $json): ?string
    {
        if ($json === null) {
            return null;
        }

        // Decode and re-encode to ensure valid JSON
        $decoded = json_decode($json, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }

        // Sanitize the decoded data
        $sanitized = self::array($decoded);

        return json_encode($sanitized);
    }

    /**
     * Sanitize CSV data
     *
     * @param string|null $csv
     * @return string|null
     */
    public static function csv(?string $csv): ?string
    {
        if ($csv === null) {
            return null;
        }

        // Remove potential formula injection
        $csv = preg_replace('/^[=+\-@]/', '', $csv);

        return self::string($csv);
    }

    /**
     * Remove all whitespace
     *
     * @param string|null $value
     * @return string|null
     */
    public static function removeWhitespace(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return preg_replace('/\s+/', '', $value);
    }

    /**
     * Sanitize slug (URL-friendly string)
     *
     * @param string|null $value
     * @return string|null
     */
    public static function slug(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        // Convert to lowercase
        $value = strtolower($value);

        // Replace non-alphanumeric with hyphens
        $value = preg_replace('/[^a-z0-9]+/', '-', $value);

        // Remove multiple consecutive hyphens
        $value = preg_replace('/-+/', '-', $value);

        // Trim hyphens from edges
        $value = trim($value, '-');

        return $value ?: null;
    }
}

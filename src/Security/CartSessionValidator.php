<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class CartSessionValidator
{
    /**
     * Validate that cart operations are coming from the same session
     */
    public function validateCartAccess(Request $request, int $cartId): bool
    {
        $session = $request->getSession();
        $storedCartId = $session->get('cart_id');

        // If there's a stored cart ID, it must match
        if ($storedCartId && $storedCartId !== $cartId) {
            return false;
        }

        return true;
    }

    /**
     * Validate request origin to prevent CSRF
     */
    public function validateOrigin(Request $request): bool
    {
        $origin = $request->headers->get('Origin');
        $referer = $request->headers->get('Referer');
        $host = $request->getSchemeAndHttpHost();

        // Allow same-origin requests
        if ($origin && str_starts_with($origin, $host)) {
            return true;
        }

        if ($referer && str_starts_with($referer, $host)) {
            return true;
        }

        // For non-browser requests (mobile apps, etc.), allow if no origin header
        if (!$origin && !$referer) {
            return true;
        }

        return false;
    }

    /**
     * Sanitize input to prevent XSS and SQL injection
     */
    public function sanitizeInput(string $input, bool $allowNewlines = false): string
    {
        // Remove null bytes
        $input = str_replace(chr(0), '', $input);

        // Remove any script tags and their content
        $input = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $input);

        // Remove HTML tags (allow safe tags only if needed)
        $input = strip_tags($input);

        // Encode any remaining special characters to HTML entities
        $input = htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, 'UTF-8', false);

        // Remove any JavaScript event handlers
        $input = preg_replace('/\s*on\w+\s*=\s*["\'].*?["\']/i', '', $input);

        // Remove javascript: protocol
        $input = preg_replace('/javascript:/i', '', $input);

        // Trim whitespace
        $input = trim($input);

        // Remove excessive newlines if not allowed
        if (!$allowNewlines) {
            $input = preg_replace('/\s+/', ' ', $input);
        }

        return $input;
    }

    /**
     * Sanitize HTML content (for rich text fields)
     * Only allows safe HTML tags
     */
    public function sanitizeHtml(string $html): string
    {
        // Define allowed tags
        $allowedTags = '<p><br><strong><em><u><ul><ol><li><a><h1><h2><h3><h4><h5><h6><blockquote><code><pre>';

        // Strip all tags except allowed
        $html = strip_tags($html, $allowedTags);

        // Remove any JavaScript event handlers
        $html = preg_replace('/\s*on\w+\s*=\s*["\'].*?["\']/i', '', $html);

        // Remove javascript: and data: protocols from links
        $html = preg_replace('/(href|src)\s*=\s*["\']?\s*(javascript|data):/i', '$1="#blocked-url"', $html);

        return $html;
    }

    /**
     * Detect spam patterns in input
     */
    public function isSpam(string $input): bool
    {
        // Check for excessive URLs
        if (preg_match_all('/(https?:\/\/|www\.)/i', $input) > 3) {
            return true;
        }

        // Check for excessive capital letters
        $capitals = preg_match_all('/[A-Z]/', $input);
        $total = strlen($input);
        if ($total > 0 && ($capitals / $total) > 0.5) {
            return true;
        }

        // Check for repeated characters (spam pattern)
        if (preg_match('/(.)\1{9,}/', $input)) {
            return true;
        }

        // Check for common spam words
        $spamWords = [
            'viagra', 'cialis', 'pharmacy', 'casino', 'poker',
            'lottery', 'prize', 'winner', 'click here', 'buy now',
            'limited time', 'act now', 'free money', 'earn money'
        ];

        $lowerInput = strtolower($input);
        foreach ($spamWords as $word) {
            if (str_contains($lowerInput, $word)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check honeypot field (should be empty if human)
     */
    public function validateHoneypot(?string $honeypotValue): bool
    {
        // Honeypot field should be empty for legitimate users
        return empty($honeypotValue);
    }

    /**
     * Validate email format
     */
    public function isValidEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validate phone number (basic validation)
     */
    public function isValidPhone(string $phone): bool
    {
        // Remove common separators
        $phone = preg_replace('/[\s\-\(\)]+/', '', $phone);

        // Check if it's a valid phone number (10-15 digits, optionally starting with +)
        return preg_match('/^\+?[0-9]{10,15}$/', $phone) === 1;
    }

    /**
     * Validate postal code for European countries
     */
    public function isValidPostalCode(string $postalCode, string $countryCode): bool
    {
        $patterns = [
            'DE' => '/^[0-9]{5}$/',
            'AT' => '/^[0-9]{4}$/',
            'CH' => '/^[0-9]{4}$/',
            'FR' => '/^[0-9]{5}$/',
            'IT' => '/^[0-9]{5}$/',
            'ES' => '/^[0-9]{5}$/',
            'NL' => '/^[0-9]{4}\s?[A-Z]{2}$/',
            'BE' => '/^[0-9]{4}$/',
            'PL' => '/^[0-9]{2}-[0-9]{3}$/',
            'GB' => '/^[A-Z]{1,2}[0-9]{1,2}[A-Z]?\s?[0-9][A-Z]{2}$/i',
        ];

        if (!isset($patterns[$countryCode])) {
            // For countries without specific pattern, just check it's not empty
            return !empty(trim($postalCode));
        }

        return preg_match($patterns[$countryCode], trim($postalCode)) === 1;
    }

    /**
     * Rate limit validation
     */
    private array $rateLimitStore = [];

    public function checkRateLimit(string $identifier, int $maxRequests = 60, int $perSeconds = 60): bool
    {
        $now = time();
        $key = $identifier;

        if (!isset($this->rateLimitStore[$key])) {
            $this->rateLimitStore[$key] = [];
        }

        // Clean old requests
        // phpcs:ignore Security.BadFunctions.CallbackFunctions -- Safe inline closure for filtering
        $this->rateLimitStore[$key] = array_filter(
            $this->rateLimitStore[$key],
            fn($timestamp) => ($now - $timestamp) < $perSeconds
        );

        // Check if limit exceeded
        if (count($this->rateLimitStore[$key]) >= $maxRequests) {
            return false;
        }

        // Add current request
        $this->rateLimitStore[$key][] = $now;

        return true;
    }

    /**
     * Validate against SQL injection patterns
     * Note: Doctrine ORM already prevents SQL injection, but this is an extra layer
     */
    public function hasSqlInjectionPatterns(string $input): bool
    {
        $patterns = [
            '/(\bUNION\b.*\bSELECT\b)/i',
            '/(\bSELECT\b.*\bFROM\b)/i',
            '/(\bINSERT\b.*\bINTO\b)/i',
            '/(\bUPDATE\b.*\bSET\b)/i',
            '/(\bDELETE\b.*\bFROM\b)/i',
            '/(\bDROP\b.*\bTABLE\b)/i',
            '/(\bCREATE\b.*\bTABLE\b)/i',
            '/(;|\-\-|\/\*|\*\/|@@)/',  // Removed single @ to allow email addresses
            '/(\bEXEC\b|\bEXECUTE\b)/i',
            '/(\bSCRIPT\b)/i',
            '/(0x[0-9a-f]+)/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $input)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Comprehensive input validation
     * Returns array of errors, empty if valid
     */
    public function validateInput(string $input, string $fieldName, array $options = []): array
    {
        $errors = [];
        $maxLength = $options['maxLength'] ?? 255;
        $minLength = $options['minLength'] ?? 0;
        $required = $options['required'] ?? false;
        $allowHtml = $options['allowHtml'] ?? false;

        // Check if required
        if ($required && empty(trim($input))) {
            $errors[] = "$fieldName is required";
            return $errors;
        }

        // Skip further validation if empty and not required
        if (empty(trim($input))) {
            return $errors;
        }

        // Check length
        if (strlen($input) > $maxLength) {
            $errors[] = "$fieldName must not exceed $maxLength characters";
        }

        if (strlen($input) < $minLength) {
            $errors[] = "$fieldName must be at least $minLength characters";
        }

        // Check for SQL injection patterns
        if ($this->hasSqlInjectionPatterns($input)) {
            $errors[] = "$fieldName contains invalid characters";
        }

        // Check for spam
        if (strlen($input) > 20 && $this->isSpam($input)) {
            $errors[] = "$fieldName appears to contain spam";
        }

        return $errors;
    }

    /**
     * Verify reCAPTCHA token
     */
    public function verifyRecaptcha(?string $token, string $secretKey): bool
    {
        if (empty($token)) {
            return false;
        }

        $url = 'https://www.google.com/recaptcha/api/siteverify';
        $data = [
            'secret' => $secretKey,
            'response' => $token
        ];

        $options = [
            'http' => [
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST',
                'content' => http_build_query($data)
            ]
        ];

        $context = stream_context_create($options);
        // phpcs:ignore Security.BadFunctions.FilesystemFunctions -- reCAPTCHA verification URL, not filesystem access
        $result = @file_get_contents($url, false, $context);

        if ($result === false) {
            return false;
        }

        $resultJson = json_decode($result, true);
        return isset($resultJson['success']) && $resultJson['success'] === true;
    }
}


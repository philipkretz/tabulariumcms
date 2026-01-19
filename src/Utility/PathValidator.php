<?php
namespace App\Utility;

final class PathValidator
{
    private function __construct() {}

    public static function isValidPath(string $path): bool
    {
        // Check for directory traversal attempts
        if (str_contains($path, '..') || str_contains($path, "\0")) {
            return false;
        }

        // Check for absolute paths
        if (str_starts_with($path, '/') || str_starts_with($path, '\\')) {
            return false;
        }

        // Check for Windows drive letters
        if (preg_match('/^[A-Za-z]:/', $path)) {
            return false;
        }

        // Check for invalid characters
        if (preg_match('/[<>:"|?*]/', $path)) {
            return false;
        }

        return true;
    }

    public static function sanitizePath(string $path): string
    {
        // Remove any directory traversal attempts
        $path = str_replace('..', '', $path);
        
        // Remove null bytes
        $path = str_replace("\0", '', $path);
        
        // Remove leading slashes/backslashes
        $path = ltrim($path, '/\\');
        
        // Normalize path separators
        $path = str_replace('\\', '/', $path);
        
        // Collapse multiple slashes
        $path = preg_replace('/\/+/', '/', $path);
        
        return $path;
    }

    public static function validateAndSanitizePath(string $path): string|false
    {
        if (!self::isValidPath($path)) {
            return false;
        }

        return self::sanitizePath($path);
    }

    public static function isWithinDirectory(string $path, string $directory): bool
    {
        // Validate and sanitize inputs first
        $sanitizedPath = self::validateAndSanitizePath($path);
        $sanitizedDirectory = self::validateAndSanitizePath($directory);

        if ($sanitizedPath === false || $sanitizedDirectory === false) {
            return false;
        }

        // Additional validation: ensure directory exists and is actually a directory
        // phpcs:ignore Security.BadFunctions.FilesystemFunctions -- Validated input
        if (!is_dir($sanitizedDirectory)) {
            return false;
        }

        // phpcs:ignore Security.BadFunctions.FilesystemFunctions -- Validated input
        $realDirectory = realpath($sanitizedDirectory);
        // phpcs:ignore Security.BadFunctions.FilesystemFunctions -- Validated input
        $realPath = realpath($sanitizedDirectory . '/' . $sanitizedPath);

        if ($realDirectory === false || $realPath === false) {
            return false;
        }

        return str_starts_with($realPath . '/', $realDirectory . '/');
    }
}
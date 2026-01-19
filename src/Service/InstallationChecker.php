<?php

namespace App\Service;

use App\Repository\UserRepository;

class InstallationChecker
{
    private string $projectDir;
    private UserRepository $userRepository;
    private ?bool $isInstalledCache = null;

    public function __construct(string $projectDir, UserRepository $userRepository)
    {
        $this->projectDir = $projectDir;
        $this->userRepository = $userRepository;
    }

    /**
     * Check if the system is installed.
     *
     * SECURITY: If the lock file exists with valid content and signature,
     * the system is considered installed. This check takes precedence over
     * database checks to prevent re-installation if database is temporarily unavailable.
     */
    public function isInstalled(): bool
    {
        if ($this->isInstalledCache !== null) {
            return $this->isInstalledCache;
        }

        $lockFile = $this->getLockFilePath();

        // phpcs:ignore Security.BadFunctions.FilesystemFunctions -- Using server-controlled project directory
        if (!file_exists($lockFile)) {
            $this->isInstalledCache = false;
            return false;
        }

        // Verify lock file has valid content and signature
        // phpcs:ignore Security.BadFunctions.FilesystemFunctions -- Using server-controlled project directory
        $content = file_get_contents($lockFile);
        $data = json_decode($content, true);

        if (!$data || !isset($data['installed_at']) || !isset($data['signature'])) {
            $this->isInstalledCache = false;
            return false;
        }

        // Verify the signature to prevent tampering
        $expectedSignature = $this->generateSignature($data['installed_at'], $data['version'] ?? '1.0.0');
        if (!hash_equals($expectedSignature, $data['signature'])) {
            $this->isInstalledCache = false;
            return false;
        }

        // Lock file is valid - system is installed
        // This is the PRIMARY check. We don't fall back to false if DB check fails.
        $this->isInstalledCache = true;
        return true;
    }

    /**
     * Mark the system as installed by creating a signed lock file.
     * This operation is FINAL and cannot be undone through the application.
     */
    public function markAsInstalled(): void
    {
        // Double-check: refuse to mark as installed if already installed
        if ($this->isInstalled()) {
            throw new \RuntimeException('System is already installed. Cannot re-install.');
        }

        $lockFile = $this->getLockFilePath();
        $installedAt = (new \DateTimeImmutable())->format('c');
        $version = '1.0.0';

        $data = [
            'installed_at' => $installedAt,
            'version' => $version,
            'signature' => $this->generateSignature($installedAt, $version),
            'php_version' => PHP_VERSION,
            'server_name' => $_SERVER['SERVER_NAME'] ?? 'unknown',
        ];

        // phpcs:ignore Security.BadFunctions.FilesystemFunctions -- Using server-controlled project directory
        file_put_contents($lockFile, json_encode($data, JSON_PRETTY_PRINT));

        // Make the lock file read-only for additional protection
        // phpcs:ignore Security.BadFunctions.FilesystemFunctions -- Using server-controlled project directory
        chmod($lockFile, 0444);

        $this->isInstalledCache = true;
    }

    /**
     * Get installation information (read-only).
     */
    public function getInstallationInfo(): ?array
    {
        if (!$this->isInstalled()) {
            return null;
        }

        $lockFile = $this->getLockFilePath();
        // phpcs:ignore Security.BadFunctions.FilesystemFunctions -- Using server-controlled project directory
        $content = file_get_contents($lockFile);
        $data = json_decode($content, true);

        // Return only safe information, not the signature
        return [
            'installed_at' => $data['installed_at'] ?? null,
            'version' => $data['version'] ?? null,
            'php_version' => $data['php_version'] ?? null,
        ];
    }

    private function getLockFilePath(): string
    {
        return $this->projectDir . '/.installed';
    }

    /**
     * Generate a signature to verify lock file integrity.
     * Uses project directory path as part of the secret to tie installation to this specific instance.
     */
    private function generateSignature(string $installedAt, string $version): string
    {
        $secret = $this->projectDir . '::TabulariumCMS::' . php_uname('n');
        return hash_hmac('sha256', $installedAt . '|' . $version, $secret);
    }
}

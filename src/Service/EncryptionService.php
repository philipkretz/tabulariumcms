<?php

namespace App\Service;

class EncryptionService
{
    private string $encryptionKey;

    public function __construct(string $appSecret)
    {
        // Derive encryption key from APP_SECRET
        $this->encryptionKey = hash('sha256', $appSecret, true);
    }

    public function encrypt(string $data): string
    {
        if (empty($data)) {
            return '';
        }

        $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $encrypted = sodium_crypto_secretbox($data, $nonce, $this->encryptionKey);

        return base64_encode($nonce . $encrypted);
    }

    public function decrypt(string $encrypted): string
    {
        if (empty($encrypted)) {
            return '';
        }

        $decoded = base64_decode($encrypted);

        if ($decoded === false || strlen($decoded) < SODIUM_CRYPTO_SECRETBOX_NONCEBYTES) {
            throw new \RuntimeException('Invalid encrypted data');
        }

        $nonce = substr($decoded, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $ciphertext = substr($decoded, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);

        $decrypted = sodium_crypto_secretbox_open($ciphertext, $nonce, $this->encryptionKey);

        if ($decrypted === false) {
            throw new \RuntimeException('Failed to decrypt password');
        }

        return $decrypted;
    }

    public function isEncrypted(string $data): bool
    {
        // Simple check: encrypted passwords are base64 and longer
        // Minimum length should be nonce size (24) + some ciphertext
        return strlen($data) > 50 && preg_match('/^[A-Za-z0-9+\/=]+$/', $data);
    }
}

<?php
// Handles all AES-256-CBC encryption/decryption used by the application
class EncryptionService {
    private const CIPHER    = 'AES-256-CBC';
    private const KEY_BYTES = 32; // 256 bits

    // Generate a cryptographically random AES key
    public static function generateKey(): string {
        return random_bytes(self::KEY_BYTES);
    }

    // Encrypt the raw AES key using the user's plain login password
    // We derive a proper key from the password via SHA-256 so any password length works
    public static function encryptKey(string $rawKey, string $password): string {
        $iv         = random_bytes(16);
        $derivedKey = hash('sha256', $password, true); // 32 bytes from password
        $encrypted  = openssl_encrypt($rawKey, self::CIPHER, $derivedKey, OPENSSL_RAW_DATA, $iv);
        // Store IV + ciphertext together, base64-encoded for DB storage
        return base64_encode($iv . $encrypted);
    }

    // Decrypt the stored AES key back to raw bytes using the user's plain password
    public static function decryptKey(string $storedKey, string $password): string {
        $data       = base64_decode($storedKey);
        $iv         = substr($data, 0, 16);
        $ciphertext = substr($data, 16);
        $derivedKey = hash('sha256', $password, true);
        return openssl_decrypt($ciphertext, self::CIPHER, $derivedKey, OPENSSL_RAW_DATA, $iv);
    }

    // Encrypt a saved password using the user's raw AES key
    public static function encryptPassword(string $plainPassword, string $rawKey): string {
        $iv        = random_bytes(16);
        $encrypted = openssl_encrypt($plainPassword, self::CIPHER, $rawKey, OPENSSL_RAW_DATA, $iv);
        return base64_encode($iv . $encrypted);
    }

    // Decrypt a saved password using the user's raw AES key
    public static function decryptPassword(string $storedPassword, string $rawKey): string {
        $data       = base64_decode($storedPassword);
        $iv         = substr($data, 0, 16);
        $ciphertext = substr($data, 16);
        $result     = openssl_decrypt($ciphertext, self::CIPHER, $rawKey, OPENSSL_RAW_DATA, $iv);
        return $result !== false ? $result : '[decryption error]';
    }
}

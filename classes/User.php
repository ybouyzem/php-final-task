<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/EncryptionService.php';

class User {
    private mysqli $db;
    private int $id;
    private string $username;
    private string $email;
    private string $encryptionKey; // the decrypted AES key, held in memory only

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // Register a new user: hash password, generate AES key, encrypt key with plain password
    public function register(string $username, string $email, string $plainPassword): bool {
        // Check uniqueness
        $stmt = $this->db->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param('ss', $username, $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            throw new RuntimeException('Username or email already taken.');
        }

        $passwordHash = password_hash($plainPassword, PASSWORD_BCRYPT);

        // Generate a random 32-byte AES key for this user (never changes)
        $rawKey = EncryptionService::generateKey();

        // Encrypt the AES key using the user's plain login password
        $encryptedKey = EncryptionService::encryptKey($rawKey, $plainPassword);

        $stmt = $this->db->prepare(
            "INSERT INTO users (username, email, password_hash, encrypted_key) VALUES (?, ?, ?, ?)"
        );
        $stmt->bind_param('ssss', $username, $email, $passwordHash, $encryptedKey);
        return $stmt->execute();
    }

    // Log in: verify password, decrypt the AES key into memory
    public function login(string $username, string $plainPassword): bool {
        $stmt = $this->db->prepare(
            "SELECT id, username, email, password_hash, encrypted_key FROM users WHERE username = ?"
        );
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        if (!$row || !password_verify($plainPassword, $row['password_hash'])) {
            return false;
        }

        $this->id       = $row['id'];
        $this->username = $row['username'];
        $this->email    = $row['email'];

        // Decrypt the stored AES key using the plain password
        $this->encryptionKey = EncryptionService::decryptKey($row['encrypted_key'], $plainPassword);

        // Store in session (key kept in session for the duration of the login)
        $_SESSION['user_id']        = $this->id;
        $_SESSION['username']       = $this->username;
        $_SESSION['encryption_key'] = $this->encryptionKey;

        return true;
    }

    // Change login password: re-encrypt the AES key with the new password
    public function changePassword(int $userId, string $oldPassword, string $newPassword): bool {
        $stmt = $this->db->prepare("SELECT password_hash, encrypted_key FROM users WHERE id = ?");
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        if (!$row || !password_verify($oldPassword, $row['password_hash'])) {
            throw new RuntimeException('Current password is incorrect.');
        }

        // Decrypt old key, re-encrypt with new password — the KEY itself never changes
        $rawKey       = EncryptionService::decryptKey($row['encrypted_key'], $oldPassword);
        $encryptedKey = EncryptionService::encryptKey($rawKey, $newPassword);
        $newHash      = password_hash($newPassword, PASSWORD_BCRYPT);

        $stmt = $this->db->prepare(
            "UPDATE users SET password_hash = ?, encrypted_key = ? WHERE id = ?"
        );
        $stmt->bind_param('ssi', $newHash, $encryptedKey, $userId);

        // Update session key too
        $_SESSION['encryption_key'] = $rawKey;

        return $stmt->execute();
    }

    public function logout(): void {
        session_destroy();
    }

    public static function getById(int $id): ?array {
        $db   = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT id, username, email, created_at FROM users WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc() ?: null;
    }
}

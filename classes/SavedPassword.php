<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/EncryptionService.php';

class SavedPassword {
    private mysqli $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // Save a new password entry; password is AES-encrypted before storage
    public function save(
        int    $userId,
        string $siteName,
        string $plainPassword,
        string $rawKey,
        string $siteUrl  = '',
        string $username = '',
        string $notes    = ''
    ): bool {
        $encryptedPassword = EncryptionService::encryptPassword($plainPassword, $rawKey);

        $stmt = $this->db->prepare(
            "INSERT INTO saved_passwords (user_id, site_name, site_url, username, password_enc, notes)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param('isssss', $userId, $siteName, $siteUrl, $username, $encryptedPassword, $notes);
        return $stmt->execute();
    }

    // Update an existing password entry
    public function update(
        int    $id,
        int    $userId,
        string $siteName,
        string $plainPassword,
        string $rawKey,
        string $siteUrl  = '',
        string $username = '',
        string $notes    = ''
    ): bool {
        $encryptedPassword = EncryptionService::encryptPassword($plainPassword, $rawKey);

        $stmt = $this->db->prepare(
            "UPDATE saved_passwords
             SET site_name = ?, site_url = ?, username = ?, password_enc = ?, notes = ?
             WHERE id = ? AND user_id = ?"
        );
        $stmt->bind_param('sssssii', $siteName, $siteUrl, $username, $encryptedPassword, $notes, $id, $userId);
        return $stmt->execute();
    }

    // Delete a password entry (only if it belongs to the user)
    public function delete(int $id, int $userId): bool {
        $stmt = $this->db->prepare("DELETE FROM saved_passwords WHERE id = ? AND user_id = ?");
        $stmt->bind_param('ii', $id, $userId);
        return $stmt->execute();
    }

    // Get all password entries for a user, decrypting each one
    public function getAllForUser(int $userId, string $rawKey): array {
        $stmt = $this->db->prepare(
            "SELECT id, site_name, site_url, username, password_enc, notes, created_at, updated_at
             FROM saved_passwords WHERE user_id = ? ORDER BY site_name ASC"
        );
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        // Decrypt passwords before returning
        foreach ($rows as &$row) {
            $row['password_plain'] = EncryptionService::decryptPassword($row['password_enc'], $rawKey);
        }
        return $rows;
    }

    // Get a single entry by ID
    public function getById(int $id, int $userId, string $rawKey): ?array {
        $stmt = $this->db->prepare(
            "SELECT id, site_name, site_url, username, password_enc, notes, created_at
             FROM saved_passwords WHERE id = ? AND user_id = ?"
        );
        $stmt->bind_param('ii', $id, $userId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        if ($row) {
            $row['password_plain'] = EncryptionService::decryptPassword($row['password_enc'], $rawKey);
        }
        return $row ?: null;
    }
}

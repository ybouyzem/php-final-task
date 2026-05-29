<?php
// Simple auth helpers used on every protected page
class Auth {
    // Call at top of every protected page
    public static function requireLogin(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['user_id'])) {
            header('Location: /login.php');
            exit;
        }
    }

    public static function userId(): int {
        return (int) ($_SESSION['user_id'] ?? 0);
    }

    public static function username(): string {
        return $_SESSION['username'] ?? '';
    }

    // The raw AES key for this user's session
    public static function encryptionKey(): string {
        return $_SESSION['encryption_key'] ?? '';
    }

    public static function isLoggedIn(): bool {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return !empty($_SESSION['user_id']);
    }
}

-- Password Manager Database Schema
CREATE DATABASE IF NOT EXISTS password_manager CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE password_manager;

-- Users table: stores login credentials and the encrypted encryption key
CREATE TABLE users (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username    VARCHAR(50)  NOT NULL UNIQUE,
    email       VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,         -- bcrypt hash of login password
    encrypted_key VARCHAR(512) NOT NULL,         -- AES encryption key, itself encrypted with user's plain password
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Saved passwords table: each record belongs to a user
CREATE TABLE saved_passwords (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNSIGNED NOT NULL,
    site_name   VARCHAR(100) NOT NULL,           -- e.g. "Facebook", "Gmail"
    site_url    VARCHAR(255) DEFAULT NULL,        -- optional URL
    username    VARCHAR(150) DEFAULT NULL,        -- account username for that site
    password_enc TEXT NOT NULL,                  -- password encrypted with the user's KEY
    notes       TEXT DEFAULT NULL,               -- optional notes
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB;

-- ============================================================
-- Migration 028: Auth enhancements
-- Adds google_id, password reset token, and email log table
-- ============================================================

DELIMITER //
CREATE PROCEDURE _auth_enhancements()
BEGIN
    -- Add google_id column for Google OAuth
    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME   = 'users'
          AND COLUMN_NAME  = 'google_id'
    ) THEN
        ALTER TABLE users ADD COLUMN google_id VARCHAR(255) NULL AFTER rdc_id;
    END IF;

    -- Add password_reset_token
    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME   = 'users'
          AND COLUMN_NAME  = 'password_reset_token'
    ) THEN
        ALTER TABLE users ADD COLUMN password_reset_token VARCHAR(255) NULL AFTER google_id;
    END IF;

    -- Add password_reset_expires
    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME   = 'users'
          AND COLUMN_NAME  = 'password_reset_expires'
    ) THEN
        ALTER TABLE users ADD COLUMN password_reset_expires DATETIME NULL AFTER password_reset_token;
    END IF;

    -- Add index on google_id
    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME   = 'users'
          AND INDEX_NAME   = 'idx_users_google_id'
    ) THEN
        CREATE INDEX idx_users_google_id ON users(google_id);
    END IF;

    -- Add index on password_reset_token
    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME   = 'users'
          AND INDEX_NAME   = 'idx_users_reset_token'
    ) THEN
        CREATE INDEX idx_users_reset_token ON users(password_reset_token);
    END IF;
END //
DELIMITER ;

CALL _auth_enhancements();
DROP PROCEDURE IF EXISTS _auth_enhancements;

-- Email log table for tracking sent emails
CREATE TABLE IF NOT EXISTS email_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    recipient_email VARCHAR(255) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    email_type VARCHAR(50) NOT NULL,
    status ENUM('sent', 'failed', 'logged') DEFAULT 'logged',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email_type (email_type),
    INDEX idx_email_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- Migration 025: Audit Logs table + is_active column on users
-- ============================================================

-- 1) Audit Logs – records all admin actions
CREATE TABLE IF NOT EXISTS audit_logs (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT NOT NULL,
    action      VARCHAR(50)  NOT NULL,   -- CREATE, UPDATE, DELETE, LOGIN, LOGOUT, TOGGLE
    entity_type VARCHAR(50)  NOT NULL,   -- user, product, order, profile, session
    entity_id   INT          NULL,
    details     TEXT         NULL,
    ip_address  VARCHAR(45)  NULL,
    created_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_audit_action     (action),
    INDEX idx_audit_entity     (entity_type, entity_id),
    INDEX idx_audit_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2) Add is_active flag to users (safe: only adds if not exists)
-- MySQL will error if column already exists; wrapped in procedure for safety
DELIMITER //
CREATE PROCEDURE _add_is_active_if_missing()
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME   = 'users'
          AND COLUMN_NAME  = 'is_active'
    ) THEN
        ALTER TABLE users ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1 AFTER rdc_id;
    END IF;
END //
DELIMITER ;

CALL _add_is_active_if_missing();
DROP PROCEDURE IF EXISTS _add_is_active_if_missing;

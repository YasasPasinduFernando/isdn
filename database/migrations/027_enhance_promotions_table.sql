-- ============================================================
-- Migration 027: Enhance promotions table for CRUD management
-- Adds is_active, description, created_at, updated_at columns
-- ============================================================

DELIMITER //
CREATE PROCEDURE _enhance_promotions_table()
BEGIN
    -- Add is_active column
    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME   = 'promotions'
          AND COLUMN_NAME  = 'is_active'
    ) THEN
        ALTER TABLE promotions ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1 AFTER end_date;
    END IF;

    -- Add description column
    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME   = 'promotions'
          AND COLUMN_NAME  = 'description'
    ) THEN
        ALTER TABLE promotions ADD COLUMN description TEXT NULL AFTER name;
    END IF;

    -- Add created_at column
    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME   = 'promotions'
          AND COLUMN_NAME  = 'created_at'
    ) THEN
        ALTER TABLE promotions ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER is_active;
    END IF;

    -- Add updated_at column
    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME   = 'promotions'
          AND COLUMN_NAME  = 'updated_at'
    ) THEN
        ALTER TABLE promotions ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at;
    END IF;
END //
DELIMITER ;

CALL _enhance_promotions_table();
DROP PROCEDURE IF EXISTS _enhance_promotions_table;

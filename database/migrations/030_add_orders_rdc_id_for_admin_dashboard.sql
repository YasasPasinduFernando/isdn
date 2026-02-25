-- ============================================
-- Add orders.rdc_id for Admin Dashboard joins
-- Safe to run multiple times
-- ============================================

-- 1) Add column if missing
SET @has_col := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'orders'
      AND COLUMN_NAME = 'rdc_id'
);
SET @sql := IF(
    @has_col = 0,
    'ALTER TABLE orders ADD COLUMN rdc_id INT NULL AFTER customer_id',
    'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 2) Add index if missing
SET @has_idx := (
    SELECT COUNT(*)
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'orders'
      AND INDEX_NAME = 'idx_orders_rdc_id'
);
SET @sql := IF(
    @has_idx = 0,
    'ALTER TABLE orders ADD INDEX idx_orders_rdc_id (rdc_id)',
    'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 3) Add FK if missing
SET @has_fk := (
    SELECT COUNT(*)
    FROM information_schema.TABLE_CONSTRAINTS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'orders'
      AND CONSTRAINT_TYPE = 'FOREIGN KEY'
      AND CONSTRAINT_NAME = 'orders_rdc_fk'
);
SET @sql := IF(
    @has_fk = 0,
    'ALTER TABLE orders ADD CONSTRAINT orders_rdc_fk FOREIGN KEY (rdc_id) REFERENCES rdcs(rdc_id) ON DELETE SET NULL ON UPDATE CASCADE',
    'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

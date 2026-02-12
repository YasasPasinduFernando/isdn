-- ============================================================
-- Migration 026: Performance indexes for Delivery Efficiency Report
-- ============================================================
-- These indexes optimize the JOIN and GROUP BY operations used in
-- the RDC-wise Delivery Efficiency Report module.
--
-- Indexing Strategy:
--   1. order_deliveries(order_id)       → speeds up JOIN to orders table
--   2. order_deliveries(completed_date) → speeds up delivery status filtering
--   3. orders(rdc_id)                   → speeds up GROUP BY rdc and RDC filter
--   4. orders(created_at)               → speeds up date range filtering
-- ============================================================

-- Wrap in procedures to safely skip if index already exists

DELIMITER //

CREATE PROCEDURE _add_indexes_if_missing()
BEGIN
    -- 1) Index on order_deliveries.order_id (for JOIN to orders)
    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME   = 'order_deliveries'
          AND INDEX_NAME   = 'idx_od_order_id'
    ) THEN
        CREATE INDEX idx_od_order_id ON order_deliveries(order_id);
    END IF;

    -- 2) Index on order_deliveries.completed_date (for status filtering)
    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME   = 'order_deliveries'
          AND INDEX_NAME   = 'idx_od_completed'
    ) THEN
        CREATE INDEX idx_od_completed ON order_deliveries(completed_date);
    END IF;

    -- 3) Index on orders.rdc_id (for GROUP BY and RDC filtering)
    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME   = 'orders'
          AND INDEX_NAME   = 'idx_orders_rdc'
    ) THEN
        CREATE INDEX idx_orders_rdc ON orders(rdc_id);
    END IF;

    -- 4) Index on orders.created_at (for date range filtering)
    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME   = 'orders'
          AND INDEX_NAME   = 'idx_orders_created'
    ) THEN
        CREATE INDEX idx_orders_created ON orders(created_at);
    END IF;
END //

DELIMITER ;

CALL _add_indexes_if_missing();
DROP PROCEDURE IF EXISTS _add_indexes_if_missing;

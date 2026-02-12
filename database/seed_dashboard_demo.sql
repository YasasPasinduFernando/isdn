-- ============================================================
-- ISDN Dashboard Demo Seed Data
-- Run AFTER the main seeder.sql
-- ============================================================
-- mysql -u root -pyasas -P 3307 isdn < database/seed_dashboard_demo.sql
-- LOGIN: headoffice1@mail.com / password   OR   hom@isdn.com / password
-- ============================================================

SET @OLD_FK = @@FOREIGN_KEY_CHECKS;
SET FOREIGN_KEY_CHECKS = 0;

-- ── Spread existing orders across recent dates ──────────────
UPDATE orders SET created_at = DATE_SUB(NOW(), INTERVAL 5 DAY), rdc_id = 1 WHERE id = 1;
UPDATE orders SET created_at = DATE_SUB(NOW(), INTERVAL 12 DAY), rdc_id = 2 WHERE id = 2;
UPDATE orders SET created_at = DATE_SUB(NOW(), INTERVAL 20 DAY), rdc_id = 3 WHERE id = 3;

-- ── Insert new orders (across 6 months, various statuses & RDCs) ──
INSERT INTO orders (customer_id, order_number, total_amount, status, rdc_id, created_at) VALUES
-- 6 months ago
(9,  'ORD-D001', 36250.00, 'delivered',   1, DATE_SUB(NOW(), INTERVAL 170 DAY)),
(10, 'ORD-D002', 44000.00, 'delivered',   2, DATE_SUB(NOW(), INTERVAL 160 DAY)),
-- 5 months ago
(11, 'ORD-D003', 15000.00, 'delivered',   3, DATE_SUB(NOW(), INTERVAL 145 DAY)),
(9,  'ORD-D004', 29000.00, 'delivered',   1, DATE_SUB(NOW(), INTERVAL 135 DAY)),
-- 4 months ago
(10, 'ORD-D005', 58500.00, 'delivered',   2, DATE_SUB(NOW(), INTERVAL 115 DAY)),
(11, 'ORD-D006', 18200.00, 'delivered',   4, DATE_SUB(NOW(), INTERVAL 105 DAY)),
(9,  'ORD-D007', 12500.00, 'cancelled',   1, DATE_SUB(NOW(), INTERVAL 100 DAY)),
-- 3 months ago
(10, 'ORD-D008', 72000.00, 'delivered',   5, DATE_SUB(NOW(), INTERVAL 85 DAY)),
(11, 'ORD-D009', 33600.00, 'delivered',   3, DATE_SUB(NOW(), INTERVAL 75 DAY)),
-- 2 months ago
(9,  'ORD-D010', 45000.00, 'delivered',   1, DATE_SUB(NOW(), INTERVAL 55 DAY)),
(10, 'ORD-D011', 21700.00, 'processing',  2, DATE_SUB(NOW(), INTERVAL 50 DAY)),
(11, 'ORD-D012', 87500.00, 'delivered',   4, DATE_SUB(NOW(), INTERVAL 45 DAY)),
-- Last month
(9,  'ORD-D013', 64000.00, 'delivered',   5, DATE_SUB(NOW(), INTERVAL 25 DAY)),
(10, 'ORD-D014', 19500.00, 'confirmed',   2, DATE_SUB(NOW(), INTERVAL 18 DAY)),
(11, 'ORD-D015', 41000.00, 'processing',  3, DATE_SUB(NOW(), INTERVAL 10 DAY)),
-- This month
(9,  'ORD-D016', 52000.00, 'pending',     1, DATE_SUB(NOW(), INTERVAL 4 DAY)),
(10, 'ORD-D017', 28500.00, 'confirmed',   2, DATE_SUB(NOW(), INTERVAL 2 DAY)),
(11, 'ORD-D018', 95000.00, 'pending',     4, DATE_SUB(NOW(), INTERVAL 1 DAY));

-- ── Get order IDs for the new orders (we rely on auto_increment) ──
SET @base = (SELECT id FROM orders WHERE order_number = 'ORD-D001');

-- ── Order items for each new order ──────────────────────────
INSERT INTO order_items (order_id, product_id, quantity, selling_price, discount) VALUES
-- ORD-D001: Cement + Steel
(@base,   1, 25, 1450.00, 0.00),
-- ORD-D002: Steel
(@base+1, 2, 20, 2200.00, 0.00),
-- ORD-D003: Sand
(@base+2, 3, 2,  7500.00, 0.00),
-- ORD-D004: Cement + Bricks
(@base+3, 1, 10, 1450.00, 0.00),
(@base+3, 6, 8,  1800.00, 0.00),
-- ORD-D005: Paint + Tiles + PVC
(@base+4, 4, 10, 3200.00, 0.00),
(@base+4, 7, 5,  4500.00, 0.00),
(@base+4, 5, 5,  1200.00, 0.00),
-- ORD-D006: Bricks + Cement
(@base+5, 6, 5,  1800.00, 0.00),
(@base+5, 1, 6,  1200.00, 200.00),
-- ORD-D007 (cancelled): Cement
(@base+6, 1, 10, 1250.00, 0.00),
-- ORD-D008: Water Tank + Tiles
(@base+7, 8, 2,  25000.00, 0.00),
(@base+7, 7, 5,  4400.00, 0.00),
-- ORD-D009: Sand + PVC + Steel
(@base+8, 3, 3,  7500.00, 0.00),
(@base+8, 5, 10, 1200.00, 100.00),
-- ORD-D010: Paint + Bricks
(@base+9, 4, 8,  3200.00, 0.00),
(@base+9, 6, 12, 1500.00, 0.00),
-- ORD-D011: Steel + Cement
(@base+10, 2, 5, 2200.00, 0.00),
(@base+10, 1, 8, 1450.00, 200.00),
-- ORD-D012: Water Tank + PVC + Tiles
(@base+11, 8, 3,  25000.00, 0.00),
(@base+11, 5, 8,  1200.00, 0.00),
(@base+11, 7, 2,  4500.00, 500.00),
-- ORD-D013: Steel + Sand + Bricks
(@base+12, 2, 15, 2200.00, 0.00),
(@base+12, 3, 2,  7500.00, 0.00),
(@base+12, 6, 10, 1800.00, 500.00),
-- ORD-D014: Paint + Tiles
(@base+13, 4, 3, 3200.00, 0.00),
(@base+13, 7, 2, 4500.00, 0.00),
-- ORD-D015: Cement + Steel + PVC
(@base+14, 1, 15, 1450.00, 0.00),
(@base+14, 2, 8,  2200.00, 200.00),
(@base+14, 5, 5,  1200.00, 0.00),
-- ORD-D016: Water Tank + Paint
(@base+15, 8, 2,  25000.00, 0.00),
(@base+15, 4, 1,  2000.00, 0.00),
-- ORD-D017: Cement + Bricks + Sand
(@base+16, 1, 10, 1450.00, 0.00),
(@base+16, 6, 5,  1800.00, 0.00),
(@base+16, 3, 1,  7500.00, 500.00),
-- ORD-D018: Steel + Water Tank + Tiles + Paint
(@base+17, 2, 20, 2200.00, 0.00),
(@base+17, 8, 1,  25000.00, 0.00),
(@base+17, 7, 4,  4500.00, 0.00),
(@base+17, 4, 2,  3200.00, 200.00);

-- ── Create low stock scenarios ──────────────────────────────
-- Make some product stocks below minimum levels
UPDATE product_stocks SET available_quantity = 30  WHERE product_id = 1 AND rdc_id = 1;  -- Cement (min=100) at North
UPDATE product_stocks SET available_quantity = 50  WHERE product_id = 2 AND rdc_id = 1;  -- Steel (min=200) at North
UPDATE product_stocks SET available_quantity = 10  WHERE product_id = 4 AND rdc_id = 2;  -- Paint (min=75) at South
UPDATE product_stocks SET available_quantity = 40  WHERE product_id = 5 AND rdc_id = 2;  -- PVC (min=150) at South
UPDATE product_stocks SET available_quantity = 100 WHERE product_id = 6 AND rdc_id = 3;  -- Bricks (min=300) at East
UPDATE product_stocks SET available_quantity = 5   WHERE product_id = 8 AND rdc_id = 5;  -- Water Tank (min=20) at Central

-- Add more stock entries for other RDCs
INSERT INTO product_stocks (product_id, rdc_id, available_quantity, last_updated) VALUES
(1, 3, 15, NOW()),   -- Cement at East, very low
(2, 4, 180, NOW()),  -- Steel at West, just below min
(3, 5, 8, NOW()),    -- Sand at Central, very low
(4, 1, 70, NOW()),   -- Paint at North, just below min
(7, 2, 25, NOW())    -- Tiles at South, below min
ON DUPLICATE KEY UPDATE available_quantity = VALUES(available_quantity), last_updated = NOW();

-- ── Delivery records (for delivered orders) ─────────────────
-- Update existing deliveries
UPDATE order_deliveries SET completed_date = DATE_ADD(delivery_date, INTERVAL 18 HOUR) WHERE id = 1;
UPDATE order_deliveries SET completed_date = DATE_ADD(delivery_date, INTERVAL 24 HOUR) WHERE id = 2;

-- Add deliveries for new delivered orders
INSERT INTO order_deliveries (order_id, delivery_date, driver_id, completed_date) VALUES
(@base,   DATE_ADD((SELECT created_at FROM orders WHERE id = @base),   INTERVAL 2 DAY), 7, DATE_ADD((SELECT created_at FROM orders WHERE id = @base),   INTERVAL 52 HOUR)),
(@base+1, DATE_ADD((SELECT created_at FROM orders WHERE id = @base+1), INTERVAL 3 DAY), 8, DATE_ADD((SELECT created_at FROM orders WHERE id = @base+1), INTERVAL 68 HOUR)),
(@base+2, DATE_ADD((SELECT created_at FROM orders WHERE id = @base+2), INTERVAL 2 DAY), 7, DATE_ADD((SELECT created_at FROM orders WHERE id = @base+2), INTERVAL 40 HOUR)),
(@base+3, DATE_ADD((SELECT created_at FROM orders WHERE id = @base+3), INTERVAL 1 DAY), 7, DATE_ADD((SELECT created_at FROM orders WHERE id = @base+3), INTERVAL 30 HOUR)),
(@base+4, DATE_ADD((SELECT created_at FROM orders WHERE id = @base+4), INTERVAL 2 DAY), 8, DATE_ADD((SELECT created_at FROM orders WHERE id = @base+4), INTERVAL 48 HOUR)),
(@base+5, DATE_ADD((SELECT created_at FROM orders WHERE id = @base+5), INTERVAL 3 DAY), 7, DATE_ADD((SELECT created_at FROM orders WHERE id = @base+5), INTERVAL 60 HOUR)),
(@base+7, DATE_ADD((SELECT created_at FROM orders WHERE id = @base+7), INTERVAL 2 DAY), 8, DATE_ADD((SELECT created_at FROM orders WHERE id = @base+7), INTERVAL 36 HOUR)),
(@base+8, DATE_ADD((SELECT created_at FROM orders WHERE id = @base+8), INTERVAL 2 DAY), 7, DATE_ADD((SELECT created_at FROM orders WHERE id = @base+8), INTERVAL 44 HOUR)),
(@base+9, DATE_ADD((SELECT created_at FROM orders WHERE id = @base+9), INTERVAL 1 DAY), 7, DATE_ADD((SELECT created_at FROM orders WHERE id = @base+9), INTERVAL 28 HOUR)),
(@base+11, DATE_ADD((SELECT created_at FROM orders WHERE id = @base+11), INTERVAL 3 DAY), 8, DATE_ADD((SELECT created_at FROM orders WHERE id = @base+11), INTERVAL 72 HOUR)),
(@base+12, DATE_ADD((SELECT created_at FROM orders WHERE id = @base+12), INTERVAL 2 DAY), 8, DATE_ADD((SELECT created_at FROM orders WHERE id = @base+12), INTERVAL 42 HOUR));

-- ── Pending stock transfers for HO approval ─────────────────
-- One already exists (TR-1002 PENDING). Add one more urgent one.
INSERT INTO stock_transfers (transfer_number, source_rdc_id, destination_rdc_id, requested_by, requested_date, request_reason, is_urgent, approval_status)
VALUES ('TR-1003', 3, 1, 5, DATE_SUB(NOW(), INTERVAL 1 DAY), 'Urgent cement restock for North RDC', 1, 'PENDING');

SET FOREIGN_KEY_CHECKS = @OLD_FK;

SELECT 'Dashboard seed data inserted successfully!' AS result;

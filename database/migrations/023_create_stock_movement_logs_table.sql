-- ============================================
-- Stock Movement Logs Table
-- ============================================

CREATE TABLE IF NOT EXISTS `stock_movement_logs` (
    `movement_id` INT PRIMARY KEY AUTO_INCREMENT,
    `rdc_id` INT NOT NULL,
    `product_id` INT NOT NULL,
    
    -- Movement details
    `movement_type` ENUM(
        'STOCK_IN',           -- New stock received
        'STOCK_OUT',          -- Sales/Orders fulfilled
        'TRANSFER_OUT',       -- Sent to another RDC
        'TRANSFER_IN',        -- Received from another RDC
        'ADJUSTMENT',         -- Manual correction
        'DAMAGED',            -- Damaged goods removed
        'RETURNED',           -- Customer returns
        'EXPIRED'             -- Expired products removed
    ) NOT NULL,
    
    `quantity` INT NOT NULL COMMENT 'Positive or negative',
    `previous_quantity` INT,
    `new_quantity` INT,
    
    -- Who made the change
    `created_by` INT,
    `created_by_role` VARCHAR(50) NOT NULL,
    `created_by_name` VARCHAR(255) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    `note` TEXT NULL,

    FOREIGN KEY (rdc_id) REFERENCES rdcs(rdc_id),
    FOREIGN KEY (product_id) REFERENCES products(product_id),
    INDEX idx_movement_type (movement_type),
    INDEX idx_created_at (created_at),
    INDEX idx_rdc_product (rdc_id, product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
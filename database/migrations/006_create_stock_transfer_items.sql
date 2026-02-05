-- ============================================
-- Stock Transfer Items (Multiple Products)
-- ============================================

CREATE TABLE IF NOT EXISTS stock_transfer_items (
    item_id INT PRIMARY KEY AUTO_INCREMENT,
    transfer_id INT NOT NULL,
    product_id INT NOT NULL,
    requested_quantity INT NOT NULL,
    remarks TEXT,
    FOREIGN KEY (transfer_id) REFERENCES stock_transfers(transfer_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id)
);
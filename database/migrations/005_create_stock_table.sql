-- ============================================
-- Stock/Inventory Table
-- ============================================
use isdn_db;

CREATE TABLE IF NOT EXISTS stock (
    stock_id INT PRIMARY KEY AUTO_INCREMENT,
    rdc_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT DEFAULT 0,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (rdc_id) REFERENCES rdcs(rdc_id),
    FOREIGN KEY (product_id) REFERENCES products(product_id),
    UNIQUE KEY unique_rdc_product (rdc_id, product_id)
);

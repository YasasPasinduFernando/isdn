-- ============================================
-- Ensure Product Categories Table Exists
-- ============================================

CREATE TABLE IF NOT EXISTS product_categories (
  category_id INT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(50) NOT NULL UNIQUE,
  description VARCHAR(255) NOT NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Seed defaults safely (idempotent)
INSERT INTO product_categories (name, description) VALUES
('Construction', 'Construction products'),
('Finishing', 'Finishing products'),
('Plumbing', 'Plumbing products'),
('Raw Material', 'Raw material products')
ON DUPLICATE KEY UPDATE description = VALUES(description);

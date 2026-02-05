
-- ============================================
-- RDCs Master Table
-- ============================================

CREATE TABLE IF NOT EXISTS rdcs (
    rdc_id INT PRIMARY KEY AUTO_INCREMENT,
    rdc_code ENUM('NORTH', 'SOUTH', 'EAST', 'WEST', 'CENTRAL') DEFAULT 'NORTH',
    rdc_name VARCHAR(100) NOT NULL,
    province VARCHAR(50),
    address TEXT,
    contact_number VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

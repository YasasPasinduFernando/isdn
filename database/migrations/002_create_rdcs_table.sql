
-- ============================================
-- RDCs Master Table
-- ============================================
USE isdn_db;

CREATE TABLE IF NOT EXISTS rdcs (
    rdc_id INT PRIMARY KEY AUTO_INCREMENT,
    rdc_code VARCHAR(20) UNIQUE NOT NULL,
    rdc_name VARCHAR(100) NOT NULL,
    province VARCHAR(50),
    address TEXT,
    contact_number VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

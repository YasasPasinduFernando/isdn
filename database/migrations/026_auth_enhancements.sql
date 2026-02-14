-- ============================================================
-- Email Log Table
-- ============================================================

CREATE TABLE IF NOT EXISTS email_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    recipient_email VARCHAR(255) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    email_type VARCHAR(50) NOT NULL,
    status ENUM('sent', 'failed', 'logged') DEFAULT 'logged',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email_type (email_type),
    INDEX idx_email_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- Users Table
-- ============================================

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM(
        'customer',
        'rdc_manager',
        'rdc_clerk',
        'rdc_sales_ref',
        'logistics_officer',
        'rdc_driver',
        'head_office_manager',
        'system_admin'
    ) DEFAULT 'customer',
    rdc_id INT NULL,

    -- OAuth Support
    google_id VARCHAR(255) NULL,

    -- Password Reset Support
    password_reset_token VARCHAR(255) NULL,
    password_reset_expires DATETIME NULL,

    is_active TINYINT(1) NOT NULL DEFAULT 1,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- Indexes
    KEY idx_users_google_id (google_id),
    KEY idx_users_reset_token (password_reset_token)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

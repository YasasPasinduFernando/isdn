-- ============================================
-- Stock Transfers Table
-- ============================================

CREATE TABLE IF NOT EXISTS stock_transfers (
    transfer_id INT PRIMARY KEY AUTO_INCREMENT,
    transfer_number VARCHAR(50) UNIQUE NOT NULL,
    
    -- Transfer Details
    source_rdc_id INT NOT NULL,
    destination_rdc_id INT NOT NULL,
    
    -- Request Info
    requested_by INT NOT NULL,
    requested_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    request_reason TEXT,
    is_urgent BOOLEAN DEFAULT FALSE,
    
    -- Approval Info
    approval_status ENUM('PENDING', 'APPROVED', 'REJECTED', 'CANCELLED') DEFAULT 'PENDING',
    approved_by INT,
    approval_date TIMESTAMP NULL,
    approval_remarks TEXT,
    
    -- Status Tracking
    transfer_status ENUM(
        'PENDING_APPROVAL',
        'DISPATCHED',
        'DELIVERED',
        'CANCELLED'
    ) DEFAULT 'PENDING_APPROVAL',
    
    current_status_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Completion
    completed_date TIMESTAMP NULL,
    receiver_name VARCHAR(255),
    delivery_notes TEXT,
    
    FOREIGN KEY (source_rdc_id) REFERENCES rdcs(rdc_id),
    FOREIGN KEY (destination_rdc_id) REFERENCES rdcs(rdc_id)
);
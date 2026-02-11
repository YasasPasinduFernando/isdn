-- ============================================
-- Transfer Status Log (Audit Trail)
-- ============================================

CREATE TABLE IF NOT EXISTS transfer_status_logs (
    log_id INT PRIMARY KEY AUTO_INCREMENT,
    transfer_id INT NOT NULL,
    previous_status VARCHAR(50),
    new_status VARCHAR(50),
    changed_by INT NOT NULL,
    change_by_role VARCHAR(50) NOT NULL,
    change_by_name VARCHAR(255) NULL,
    changed_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (transfer_id) REFERENCES stock_transfers(transfer_id)
);
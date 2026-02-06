-- ============================================
-- Transfer Status Log (Audit Trail)
-- ============================================

CREATE TABLE IF NOT EXISTS transfer_status_logs (
    log_id INT PRIMARY KEY AUTO_INCREMENT,
    transfer_id INT NOT NULL,
    previous_status VARCHAR(50),
    new_status VARCHAR(50),
    changed_by INT NOT NULL,
    changed_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    remarks TEXT,
    FOREIGN KEY (transfer_id) REFERENCES stock_transfers(transfer_id)
);

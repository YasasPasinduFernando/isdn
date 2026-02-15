-- ============================================================
-- Audit Logs table
-- ============================================================

CREATE TABLE IF NOT EXISTS audit_logs (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT NOT NULL,
    action      VARCHAR(50)  NOT NULL,   -- CREATE, UPDATE, DELETE, LOGIN, LOGOUT, TOGGLE
    entity_type VARCHAR(50)  NOT NULL,   -- user, product, order, profile, session
    entity_id   INT          NULL,
    details     TEXT         NULL,
    ip_address  VARCHAR(45)  NULL,
    created_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_audit_action     (action),
    INDEX idx_audit_entity     (entity_type, entity_id),
    INDEX idx_audit_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

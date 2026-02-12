<?php
require_once __DIR__ . '/config/database.php';
echo "Connected OK\n";

try {
    // Drop procedure if exists from previous run
    $pdo->exec('DROP PROCEDURE IF EXISTS _auth_enhancements');

    $proc = <<<'SQL'
CREATE PROCEDURE _auth_enhancements()
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME   = 'users'
          AND COLUMN_NAME  = 'google_id'
    ) THEN
        ALTER TABLE users ADD COLUMN google_id VARCHAR(255) NULL AFTER rdc_id;
    END IF;

    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME   = 'users'
          AND COLUMN_NAME  = 'password_reset_token'
    ) THEN
        ALTER TABLE users ADD COLUMN password_reset_token VARCHAR(255) NULL AFTER google_id;
    END IF;

    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME   = 'users'
          AND COLUMN_NAME  = 'password_reset_expires'
    ) THEN
        ALTER TABLE users ADD COLUMN password_reset_expires DATETIME NULL AFTER password_reset_token;
    END IF;

    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME   = 'users'
          AND INDEX_NAME   = 'idx_users_google_id'
    ) THEN
        CREATE INDEX idx_users_google_id ON users(google_id);
    END IF;

    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME   = 'users'
          AND INDEX_NAME   = 'idx_users_reset_token'
    ) THEN
        CREATE INDEX idx_users_reset_token ON users(password_reset_token);
    END IF;
END
SQL;

    $pdo->exec($proc);
    echo "Procedure created\n";

    $pdo->exec('CALL _auth_enhancements()');
    echo "Auth columns added\n";

    $pdo->exec('DROP PROCEDURE IF EXISTS _auth_enhancements');
    echo "Cleanup done\n";

    // Create email_logs table
    $pdo->exec("CREATE TABLE IF NOT EXISTS email_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        recipient_email VARCHAR(255) NOT NULL,
        subject VARCHAR(255) NOT NULL,
        email_type VARCHAR(50) NOT NULL,
        status ENUM('sent', 'failed', 'logged') DEFAULT 'logged',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_email_type (email_type),
        INDEX idx_email_created (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "email_logs table created\n";

    // Verify users table
    $cols = $pdo->query('DESCRIBE users')->fetchAll(PDO::FETCH_ASSOC);
    echo "\nUpdated users table columns:\n";
    foreach ($cols as $c) {
        echo "  " . str_pad($c['Field'], 25) . $c['Type'] . "\n";
    }

    // Verify email_logs table
    $cols2 = $pdo->query('DESCRIBE email_logs')->fetchAll(PDO::FETCH_ASSOC);
    echo "\nemail_logs table:\n";
    foreach ($cols2 as $c) {
        echo "  " . str_pad($c['Field'], 25) . $c['Type'] . "\n";
    }

    echo "\nMigration 028 complete!\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

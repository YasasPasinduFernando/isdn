<?php
$pdo = new PDO('mysql:host=localhost;port=3307;dbname=isdn', 'root', 'yasas', [
    PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

echo "=== Admin users and their password hashes ===\n";
$rows = $pdo->query("SELECT id, username, email, password, role FROM users WHERE role IN ('system_admin','head_office_manager')")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    echo "\n  id={$r['id']} user={$r['username']} email={$r['email']} role={$r['role']}\n";
    echo "  hash={$r['password']}\n";
    // Test common passwords
    foreach (['password', 'Password', 'password123', 'admin', 'admin123', '123456', 'Password@123'] as $pw) {
        if (password_verify($pw, $r['password'])) {
            echo "  >>> PASSWORD IS: {$pw}\n";
        }
    }
}

<?php
// ============================================================
// Mail Configuration
// ============================================================

// Mail method: 'smtp' (Gmail SMTP), 'php_mail' (built-in), or 'log' (dev - writes to file)
define('MAIL_METHOD', 'smtp');

// Sender details (used as the From header)
define('MAIL_FROM_EMAIL', getenv('MAIL_FROM_EMAIL') ?: 'no-reply@example.com');
define('MAIL_FROM_NAME', 'ISDN - IslandLink Distribution');

// ── SMTP Configuration (Gmail) ──────────────────────────────
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 465);
define('SMTP_USERNAME', getenv('SMTP_USERNAME') ?: '');
define('SMTP_PASSWORD', getenv('SMTP_PASSWORD') ?: '');

// Log directory (used when MAIL_METHOD = 'log')
define('MAIL_LOG_DIR', __DIR__ . '/../logs/emails/');

// ============================================================
// Google OAuth Configuration
// ============================================================
define('GOOGLE_CLIENT_ID', getenv('GOOGLE_CLIENT_ID') ?: '');
define('GOOGLE_CLIENT_SECRET', getenv('GOOGLE_CLIENT_SECRET') ?: '');

// Build the Google redirect URI dynamically from the request
$_googleScheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$_googleHost   = $_SERVER['HTTP_HOST'] ?? 'localhost';
define('GOOGLE_REDIRECT_URI', $_googleScheme . '://' . $_googleHost . '/isdn/controllers/AuthController.php?action=google_callback');

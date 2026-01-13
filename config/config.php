<?php
// Application Configuration
define('APP_NAME', 'ISDN - IslandLink Sales Distribution Network');
define('APP_URL', 'http://localhost/isdn');
define('BASE_PATH', '/isdn');
define('SITE_KEY', 'your-secret-key-here');

// Timezone
date_default_timezone_set('Asia/Colombo');

// Session Configuration
ini_set('session.cookie_httponly', 1);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

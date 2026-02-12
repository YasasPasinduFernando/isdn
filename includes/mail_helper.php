<?php
/**
 * Mail Helper - Sends HTML emails via SMTP, PHP mail(), or logs to file
 *
 * Supports: Welcome emails, Login notifications, Password reset emails
 */

require_once __DIR__ . '/../config/mail_config.php';
require_once __DIR__ . '/SmtpMailer.php';

/**
 * Send an HTML email
 */
function send_email(string $to, string $subject, string $htmlBody, string $emailType = 'general'): bool
{
    global $pdo;

    $status = 'failed';

    if (MAIL_METHOD === 'log') {
        // Development mode: write email to log file
        $logDir = MAIL_LOG_DIR;
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        $filename = $logDir . date('Y-m-d_H-i-s') . '_' . $emailType . '_' . md5($to . time()) . '.html';
        $logContent  = "<!-- TO: {$to} -->\n";
        $logContent .= "<!-- SUBJECT: {$subject} -->\n";
        $logContent .= "<!-- TYPE: {$emailType} -->\n";
        $logContent .= "<!-- DATE: " . date('Y-m-d H:i:s') . " -->\n\n";
        $logContent .= $htmlBody;

        if (file_put_contents($filename, $logContent)) {
            $status = 'logged';
        }
    } elseif (MAIL_METHOD === 'smtp') {
        // SMTP mode: send via Gmail SMTP
        $mailer = new SmtpMailer(
            SMTP_HOST,
            SMTP_PORT,
            SMTP_USERNAME,
            SMTP_PASSWORD,
            MAIL_FROM_EMAIL,
            MAIL_FROM_NAME
        );

        if ($mailer->send($to, $subject, $htmlBody)) {
            $status = 'sent';
        } else {
            // Log the SMTP error for debugging
            error_log('SMTP Error: ' . $mailer->getLastError());
        }
    } else {
        // Fallback: PHP mail()
        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "From: " . MAIL_FROM_NAME . " <" . MAIL_FROM_EMAIL . ">\r\n";
        $headers .= "Reply-To: " . MAIL_FROM_EMAIL . "\r\n";
        $headers .= "X-Mailer: ISDN-PHP\r\n";

        if (@mail($to, $subject, $htmlBody, $headers)) {
            $status = 'sent';
        }
    }

    // Log to database
    try {
        if (isset($pdo)) {
            $stmt = $pdo->prepare(
                "INSERT INTO email_logs (recipient_email, subject, email_type, status) VALUES (?, ?, ?, ?)"
            );
            $stmt->execute([$to, $subject, $emailType, $status]);
        }
    } catch (Exception $e) {
        // Silently ignore logging errors
    }

    return $status !== 'failed';
}

/**
 * Build the common email wrapper template
 */
function email_template(string $title, string $bodyContent): string
{
    return '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . htmlspecialchars($title) . '</title>
</head>
<body style="margin:0;padding:0;background-color:#f0f4f8;font-family:\'Segoe UI\',Tahoma,Geneva,Verdana,sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f0f4f8;padding:40px 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08);">
                    <!-- Header -->
                    <tr>
                        <td style="background:linear-gradient(135deg,#0d9488,#059669);padding:32px 40px;text-align:center;">
                            <h1 style="margin:0;color:#ffffff;font-size:24px;font-weight:700;letter-spacing:-0.5px;">
                                ISDN
                            </h1>
                            <p style="margin:4px 0 0;color:rgba(255,255,255,0.85);font-size:13px;">
                                IslandLink Sales Distribution Network
                            </p>
                        </td>
                    </tr>
                    <!-- Body -->
                    <tr>
                        <td style="padding:36px 40px;">
                            ' . $bodyContent . '
                        </td>
                    </tr>
                    <!-- Footer -->
                    <tr>
                        <td style="background-color:#f8fafc;padding:24px 40px;text-align:center;border-top:1px solid #e2e8f0;">
                            <p style="margin:0;color:#94a3b8;font-size:12px;">
                                &copy; ' . date('Y') . ' ISDN - IslandLink Sales Distribution Network
                            </p>
                            <p style="margin:4px 0 0;color:#cbd5e1;font-size:11px;">
                                This is an automated email. Please do not reply.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>';
}

/**
 * Send welcome email to a newly registered customer
 */
function send_welcome_email(string $email, string $username): bool
{
    $loginUrl = APP_URL . '/index.php?page=login';

    $body = '
        <h2 style="margin:0 0 16px;color:#1e293b;font-size:22px;">Welcome to ISDN, ' . htmlspecialchars($username) . '!</h2>
        <p style="color:#475569;font-size:15px;line-height:1.6;margin:0 0 20px;">
            Thank you for creating your account with IslandLink Sales Distribution Network.
            You can now browse products, place orders, and track deliveries.
        </p>
        <div style="background-color:#f0fdf4;border-left:4px solid #22c55e;padding:16px 20px;border-radius:8px;margin:20px 0;">
            <p style="margin:0;color:#166534;font-size:14px;font-weight:600;">Your Account Details</p>
            <p style="margin:8px 0 0;color:#15803d;font-size:14px;">
                Email: <strong>' . htmlspecialchars($email) . '</strong><br>
                Username: <strong>' . htmlspecialchars($username) . '</strong><br>
                Role: <strong>Customer</strong>
            </p>
        </div>
        <div style="text-align:center;margin:28px 0;">
            <a href="' . htmlspecialchars($loginUrl) . '"
               style="display:inline-block;background:linear-gradient(135deg,#0d9488,#059669);color:#ffffff;text-decoration:none;padding:14px 36px;border-radius:10px;font-weight:700;font-size:15px;">
                Login to Your Account
            </a>
        </div>
        <p style="color:#94a3b8;font-size:13px;margin:0;">
            If you did not create this account, please ignore this email.
        </p>';

    $html = email_template('Welcome to ISDN', $body);
    return send_email($email, 'Welcome to ISDN - Your Account is Ready!', $html, 'welcome');
}

/**
 * Send login notification email
 */
function send_login_notification(string $email, string $username, string $role): bool
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    $time = date('M d, Y \a\t h:i A');
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    $browser = get_browser_name($userAgent);

    $body = '
        <h2 style="margin:0 0 16px;color:#1e293b;font-size:22px;">Login Detected</h2>
        <p style="color:#475569;font-size:15px;line-height:1.6;margin:0 0 20px;">
            Hello ' . htmlspecialchars($username) . ', we detected a new login to your ISDN account.
        </p>
        <div style="background-color:#eff6ff;border-left:4px solid #3b82f6;padding:16px 20px;border-radius:8px;margin:20px 0;">
            <p style="margin:0;color:#1e40af;font-size:14px;font-weight:600;">Login Details</p>
            <table style="margin:8px 0 0;color:#1d4ed8;font-size:13px;" cellpadding="4">
                <tr><td style="color:#64748b;">Time:</td><td><strong>' . $time . '</strong></td></tr>
                <tr><td style="color:#64748b;">IP Address:</td><td><strong>' . htmlspecialchars($ip) . '</strong></td></tr>
                <tr><td style="color:#64748b;">Browser:</td><td><strong>' . htmlspecialchars($browser) . '</strong></td></tr>
                <tr><td style="color:#64748b;">Role:</td><td><strong>' . htmlspecialchars(ucwords(str_replace('_', ' ', $role))) . '</strong></td></tr>
            </table>
        </div>
        <p style="color:#ef4444;font-size:13px;margin:20px 0 0;">
            If this was not you, please change your password immediately or contact support.
        </p>';

    $html = email_template('Login Notification', $body);
    return send_email($email, 'ISDN - New Login to Your Account', $html, 'login_notification');
}

/**
 * Send password reset email with a secure link
 */
function send_password_reset_email(string $email, string $username, string $token): bool
{
    $resetUrl = APP_URL . '/index.php?page=reset-password&token=' . urlencode($token);

    $body = '
        <h2 style="margin:0 0 16px;color:#1e293b;font-size:22px;">Password Reset Request</h2>
        <p style="color:#475569;font-size:15px;line-height:1.6;margin:0 0 20px;">
            Hello ' . htmlspecialchars($username) . ', we received a request to reset your password.
            Click the button below to set a new password.
        </p>
        <div style="text-align:center;margin:28px 0;">
            <a href="' . htmlspecialchars($resetUrl) . '"
               style="display:inline-block;background:linear-gradient(135deg,#0d9488,#059669);color:#ffffff;text-decoration:none;padding:14px 36px;border-radius:10px;font-weight:700;font-size:15px;">
                Reset My Password
            </a>
        </div>
        <div style="background-color:#fefce8;border-left:4px solid #eab308;padding:16px 20px;border-radius:8px;margin:20px 0;">
            <p style="margin:0;color:#854d0e;font-size:13px;">
                This link will expire in <strong>1 hour</strong>. If you did not request a password reset,
                please ignore this email. Your password will remain unchanged.
            </p>
        </div>
        <p style="color:#94a3b8;font-size:12px;margin:20px 0 0;">
            If the button does not work, copy and paste this URL into your browser:<br>
            <span style="color:#64748b;word-break:break-all;">' . htmlspecialchars($resetUrl) . '</span>
        </p>';

    $html = email_template('Reset Your Password', $body);
    return send_email($email, 'ISDN - Password Reset Request', $html, 'password_reset');
}

/**
 * Send notification when customer is registered via Google OAuth
 */
function send_google_welcome_email(string $email, string $username): bool
{
    $loginUrl = APP_URL . '/index.php?page=login';

    $body = '
        <h2 style="margin:0 0 16px;color:#1e293b;font-size:22px;">Welcome to ISDN, ' . htmlspecialchars($username) . '!</h2>
        <p style="color:#475569;font-size:15px;line-height:1.6;margin:0 0 20px;">
            Your account has been created using your Google account.
            You can now log in anytime using the "Continue with Google" button.
        </p>
        <div style="background-color:#f0fdf4;border-left:4px solid #22c55e;padding:16px 20px;border-radius:8px;margin:20px 0;">
            <p style="margin:0;color:#166534;font-size:14px;font-weight:600;">Your Account Details</p>
            <p style="margin:8px 0 0;color:#15803d;font-size:14px;">
                Email: <strong>' . htmlspecialchars($email) . '</strong><br>
                Username: <strong>' . htmlspecialchars($username) . '</strong><br>
                Role: <strong>Customer</strong><br>
                Login Method: <strong>Google</strong>
            </p>
        </div>
        <div style="text-align:center;margin:28px 0;">
            <a href="' . htmlspecialchars($loginUrl) . '"
               style="display:inline-block;background:linear-gradient(135deg,#0d9488,#059669);color:#ffffff;text-decoration:none;padding:14px 36px;border-radius:10px;font-weight:700;font-size:15px;">
                Go to ISDN
            </a>
        </div>';

    $html = email_template('Welcome to ISDN', $body);
    return send_email($email, 'Welcome to ISDN - Google Account Linked!', $html, 'welcome_google');
}

/**
 * Helper: extract browser name from User-Agent string
 */
function get_browser_name(string $userAgent): string
{
    if (strpos($userAgent, 'Edg') !== false) return 'Microsoft Edge';
    if (strpos($userAgent, 'Chrome') !== false) return 'Google Chrome';
    if (strpos($userAgent, 'Firefox') !== false) return 'Mozilla Firefox';
    if (strpos($userAgent, 'Safari') !== false) return 'Apple Safari';
    if (strpos($userAgent, 'Opera') !== false || strpos($userAgent, 'OPR') !== false) return 'Opera';
    return 'Unknown Browser';
}

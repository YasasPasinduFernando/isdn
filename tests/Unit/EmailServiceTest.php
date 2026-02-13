<?php

declare(strict_types=1);

namespace Isdn\Tests\Unit;

use PHPUnit\Framework\TestCase;
use PDO;
use PDOStatement;

// Define constants before loading mail_helper (avoids real config)
if (!defined('MAIL_METHOD')) {
    define('MAIL_METHOD', 'log');
}
if (!defined('MAIL_FROM_EMAIL')) {
    define('MAIL_FROM_EMAIL', 'test@example.com');
}
if (!defined('MAIL_FROM_NAME')) {
    define('MAIL_FROM_NAME', 'ISDN Test');
}
if (!defined('MAIL_LOG_DIR')) {
    define('MAIL_LOG_DIR', sys_get_temp_dir() . '/isdn_test_emails/');
}
if (!defined('APP_URL')) {
    define('APP_URL', 'http://localhost');
}

$originalErrorReporting = error_reporting();
error_reporting($originalErrorReporting & ~E_WARNING);
require_once dirname(__DIR__, 2) . '/includes/mail_helper.php';
error_reporting($originalErrorReporting);

/**
 * Email service unit tests for ISDN
 * Tests email template and mail-helper wrappers with log-mode delivery only.
 * Does NOT send real emails.
 */
class EmailServiceTest extends TestCase
{
    protected function setUp(): void
    {
        $this->cleanMailLogDir();
        unset($_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']);
    }

    protected function tearDown(): void
    {
        unset($_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']);
        unset($GLOBALS['pdo']);
    }

    public function testEmailTemplateGeneration(): void
    {
        $title = 'Test Email Title';
        $body = '<p>Test body content</p>';

        $html = \email_template($title, $body);

        $this->assertStringContainsString($title, $html);
        $this->assertStringContainsString($body, $html);
        $this->assertStringContainsString('<!DOCTYPE html>', $html);
        $this->assertStringContainsString('ISDN', $html);
        $this->assertStringContainsString('IslandLink', $html);
        $this->assertStringContainsString((string) date('Y'), $html);
    }

    public function testEmailTemplateEscapesTitleAndKeepsBodyMarkup(): void
    {
        $title = '<script>alert("xss")</script>';
        $body = '<strong>Safe body</strong>';

        $html = \email_template($title, $body);

        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html, 'Title should be HTML-escaped');
        $this->assertStringContainsString('<strong>Safe body</strong>', $html, 'Body is trusted HTML content');
    }

    public function testSendWelcomeEmailLogsExpectedTemplateData(): void
    {
        $sent = \send_welcome_email('new.user@example.com', 'Alice <Admin>');
        $this->assertTrue($sent);

        $content = $this->readLatestLogFile();
        $this->assertStringContainsString('<!-- TYPE: welcome -->', $content);
        $this->assertStringContainsString('Welcome to ISDN - Your Account is Ready!', $content);
        $this->assertStringContainsString('Alice &lt;Admin&gt;', $content);
        $this->assertStringContainsString('Login to Your Account', $content);
    }

    public function testSendLoginNotificationUsesFallbackServerValuesAndFormatsRole(): void
    {
        $sent = \send_login_notification('login.user@example.com', 'Sam', 'head_office_manager');
        $this->assertTrue($sent);

        $content = $this->readLatestLogFile();
        $this->assertStringContainsString('<!-- TYPE: login_notification -->', $content);
        $this->assertStringContainsString('IP Address:</td><td><strong>Unknown</strong>', $content);
        $this->assertStringContainsString('Browser:</td><td><strong>Unknown Browser</strong>', $content);
        $this->assertStringContainsString('Role:</td><td><strong>Head Office Manager</strong>', $content);
    }

    public function testSendPasswordResetEmailEncodesTokenInResetUrl(): void
    {
        $token = 'a b+c/&=?';
        $sent = \send_password_reset_email('reset.user@example.com', 'Reset User', $token);
        $this->assertTrue($sent);

        $encodedToken = urlencode($token);
        $content = $this->readLatestLogFile();
        $this->assertStringContainsString('<!-- TYPE: password_reset -->', $content);
        $this->assertStringContainsString('token=' . $encodedToken, $content);
        $this->assertStringContainsString('Reset My Password', $content);
    }

    public function testSendPasswordResetEmailWithEmptyTokenStillGeneratesEmail(): void
    {
        $sent = \send_password_reset_email('reset.user@example.com', 'Reset User', '');
        $this->assertTrue($sent);

        $content = $this->readLatestLogFile();
        $this->assertStringContainsString('token=', $content);
    }

    public function testSendGoogleWelcomeEmailUsesExpectedTemplateSections(): void
    {
        $sent = \send_google_welcome_email('google.user@example.com', 'Google User');
        $this->assertTrue($sent);

        $content = $this->readLatestLogFile();
        $this->assertStringContainsString('<!-- TYPE: welcome_google -->', $content);
        $this->assertStringContainsString('Login Method: <strong>Google</strong>', $content);
        $this->assertStringContainsString('Welcome to ISDN - Google Account Linked!', $content);
    }

    public function testSendEmailCreatesMailLogDirectoryWhenMissing(): void
    {
        $dir = rtrim(MAIL_LOG_DIR, '\\/') . DIRECTORY_SEPARATOR;
        $files = glob($dir . '*.html') ?: [];
        foreach ($files as $file) {
            @unlink($file);
        }
        if (is_dir($dir)) {
            @rmdir($dir);
        }
        $this->assertDirectoryDoesNotExist($dir);

        $sent = \send_welcome_email('mkdir@example.com', 'Mkdir User');
        $this->assertTrue($sent);
        $this->assertDirectoryExists($dir);
    }

    public function testSendEmailWritesToDatabaseLogWhenPdoIsAvailable(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->expects($this->once())
            ->method('execute')
            ->with($this->callback(static function (array $params): bool {
                return $params[0] === 'dblog@example.com'
                    && $params[1] === 'Welcome to ISDN - Your Account is Ready!'
                    && $params[2] === 'welcome'
                    && $params[3] === 'logged';
            }))
            ->willReturn(true);

        $pdo = $this->createMock(PDO::class);
        $pdo->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('INSERT INTO email_logs'))
            ->willReturn($stmt);

        $GLOBALS['pdo'] = $pdo;
        $this->assertTrue(\send_welcome_email('dblog@example.com', 'DB User'));
    }

    public function testSendEmailIgnoresDatabaseLogExceptions(): void
    {
        $pdo = $this->createMock(PDO::class);
        $pdo->expects($this->once())
            ->method('prepare')
            ->willThrowException(new \Exception('DB unavailable'));

        $GLOBALS['pdo'] = $pdo;
        $this->assertTrue(\send_welcome_email('dberror@example.com', 'DB Error User'));
    }

    public function testTokenGenerationFormat(): void
    {
        $token = bin2hex(random_bytes(32));
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $token);
        $this->assertEquals(64, strlen($token));
    }

    public function testLoginNotificationMessageFormatting(): void
    {
        $_SERVER['REMOTE_ADDR'] = '192.168.1.1';
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0';

        $email = 'user@example.com';
        $username = 'testuser';
        $role = 'customer';

        // Use reflection or call the internal logic: get_browser_name is used in send_login_notification
        $browser = \get_browser_name($_SERVER['HTTP_USER_AGENT']);
        $this->assertEquals('Google Chrome', $browser);

        $time = date('M d, Y \a\t h:i A');
        $this->assertNotEmpty($time);
    }

    public function testGetBrowserNameDetectsChrome(): void
    {
        $this->assertEquals('Google Chrome', \get_browser_name('Mozilla/5.0 Chrome/120.0'));
    }

    public function testGetBrowserNameDetectsFirefox(): void
    {
        $this->assertEquals('Mozilla Firefox', \get_browser_name('Mozilla/5.0 Firefox/121.0'));
    }

    public function testGetBrowserNameDetectsEdge(): void
    {
        $this->assertEquals('Microsoft Edge', \get_browser_name('Mozilla/5.0 Edg/120.0'));
    }

    public function testGetBrowserNameDetectsSafariAndOpera(): void
    {
        $this->assertEquals('Apple Safari', \get_browser_name('Mozilla/5.0 Safari/605.1.15'));
        $this->assertEquals('Opera', \get_browser_name('Mozilla/5.0 OPR/108.0'));
    }

    public function testGetBrowserNamePrioritizesEdgeOverChrome(): void
    {
        $agent = 'Mozilla/5.0 Chrome/120.0.0.0 Safari/537.36 Edg/120.0.0.0';
        $this->assertEquals('Microsoft Edge', \get_browser_name($agent));
    }

    public function testGetBrowserNameReturnsUnknownForUnrecognized(): void
    {
        $this->assertEquals('Unknown Browser', \get_browser_name('CustomBot/1.0'));
    }

    private function cleanMailLogDir(): void
    {
        $dir = rtrim(MAIL_LOG_DIR, '\\/') . DIRECTORY_SEPARATOR;
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $files = glob($dir . '*.html') ?: [];
        foreach ($files as $file) {
            @unlink($file);
        }
    }

    private function readLatestLogFile(): string
    {
        $dir = rtrim(MAIL_LOG_DIR, '\\/') . DIRECTORY_SEPARATOR;
        $files = glob($dir . '*.html') ?: [];
        $this->assertNotEmpty($files, 'Expected at least one logged email file');

        sort($files);
        $lastFile = end($files);
        $this->assertIsString($lastFile);

        $content = file_get_contents($lastFile);
        $this->assertNotFalse($content);
        return $content;
    }
}

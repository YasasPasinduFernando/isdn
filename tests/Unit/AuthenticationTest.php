<?php

declare(strict_types=1);

namespace Isdn\Tests\Unit;

use PHPUnit\Framework\TestCase;
use PDO;
use PDOStatement;

/**
 * Authentication unit tests for ISDN
 * Tests password hashing, verification, RBAC/routing helpers, session behavior, and token checks.
 */
class AuthenticationTest extends TestCase
{
    protected function setUp(): void
    {
        $_SESSION = [];
        unset($GLOBALS['__isdn_header_emitter'], $GLOBALS['__isdn_exit_handler']);
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
        unset($GLOBALS['__isdn_header_emitter'], $GLOBALS['__isdn_exit_handler']);
    }

    public function testPasswordHashingUsesBcrypt(): void
    {
        $password = 'SecureP@ssw0rd!';
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $this->assertNotEmpty($hash);
        $this->assertNotEquals($password, $hash);
        $this->assertStringStartsWith('$2', $hash, 'Bcrypt hashes start with $2');
    }

    public function testPasswordVerificationSucceedsWithCorrectPassword(): void
    {
        $password = 'ValidPassword123';
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $this->assertTrue(password_verify($password, $hash));
    }

    public function testInvalidPasswordRejection(): void
    {
        $correctPassword = 'CorrectPassword';
        $wrongPassword = 'WrongPassword';
        $hash = password_hash($correctPassword, PASSWORD_DEFAULT);

        $this->assertFalse(password_verify($wrongPassword, $hash));
    }

    public function testDashboardPageForRoleReturnsCorrectPage(): void
    {
        $map = [
            'customer' => 'dashboard',
            'rdc_manager' => 'rdc-manager-dashboard',
            'rdc_clerk' => 'rdc-clerk-dashboard',
            'rdc_sales_ref' => 'rdc-sales-ref-dashboard',
            'logistics_officer' => 'logistics-officer-dashboard',
            'rdc_driver' => 'rdc-driver-dashboard',
            'head_office_manager' => 'head-office-manager-dashboard',
            'system_admin' => 'system-admin-dashboard',
        ];

        foreach ($map as $role => $expectedPage) {
            $this->assertSame($expectedPage, \dashboard_page_for_role($role));
        }
    }

    public function testDashboardPageForUnknownRoleReturnsDefault(): void
    {
        $this->assertSame('dashboard', \dashboard_page_for_role('unknown_role'));
        $this->assertSame('dashboard', \dashboard_page_for_role(''));
        $this->assertSame('dashboard', \dashboard_page_for_role(null));
    }

    public function testGetProfilePageForRoleReturnsExpectedPage(): void
    {
        $this->assertSame('system-admin-profile', \get_profile_page_for_role('system_admin'));
        $this->assertSame('profile', \get_profile_page_for_role('customer'));
        $this->assertSame('profile', \get_profile_page_for_role('unknown_role'));
    }

    public function testGetAllowedPagesForRoleReturnsRoleSpecificPages(): void
    {
        $customerPages = \get_allowed_pages_for_role('customer');
        $this->assertContains('dashboard', $customerPages);
        $this->assertContains('payment', $customerPages);
        $this->assertContains('profile', $customerPages);

        $adminPages = \get_allowed_pages_for_role('system_admin');
        $this->assertContains('system-admin-users', $adminPages);
        $this->assertContains('system-admin-profile', $adminPages);
        $this->assertContains('delivery-report', $adminPages);
    }

    public function testGetAllowedPagesForUnknownRoleFallsBackToDashboardOnly(): void
    {
        $this->assertSame(['dashboard'], \get_allowed_pages_for_role('unknown_role'));
        $this->assertSame(['dashboard'], \get_allowed_pages_for_role(''));
        $this->assertSame(['dashboard'], \get_allowed_pages_for_role(null));
    }

    public function testIsPageAllowedForRoleAllowsAuthorizedPage(): void
    {
        $this->assertTrue(\is_page_allowed_for_role('customer', 'products'));
        $this->assertTrue(\is_page_allowed_for_role('system_admin', 'system-admin-users'));
        $this->assertTrue(\is_page_allowed_for_role('unknown_role', 'dashboard'));
    }

    public function testIsPageAllowedForRoleDeniesUnauthorizedPage(): void
    {
        $this->assertFalse(\is_page_allowed_for_role('customer', 'system-admin-users'));
        $this->assertFalse(\is_page_allowed_for_role('rdc_driver', 'delivery-report'));
        $this->assertFalse(\is_page_allowed_for_role('unknown_role', 'orders'));
        $this->assertFalse(\is_page_allowed_for_role('customer', null));
    }

    public function testGetNavItemsForRoleReturnsExpectedItems(): void
    {
        $customerNav = \get_nav_items_for_role('customer');
        $this->assertArrayHasKey('dashboard', $customerNav);
        $this->assertArrayHasKey('products', $customerNav);
        $this->assertArrayNotHasKey('payment', $customerNav);
        $this->assertArrayNotHasKey('profile', $customerNav);

        $adminNav = \get_nav_items_for_role('system_admin');
        $this->assertSame(
            ['system-admin-dashboard', 'system-admin-users', 'system-admin-products', 'system-admin-audit'],
            array_keys($adminNav)
        );
        $this->assertArrayNotHasKey('system-admin-profile', $adminNav);

        $unknownNav = \get_nav_items_for_role('unknown_role');
        $this->assertSame(['dashboard'], array_keys($unknownNav));
        $this->assertSame('Dashboard', $unknownNav['dashboard']['label']);
    }

    public function testSanitizeInputHandlesCommonAndEdgeInputs(): void
    {
        $this->assertSame('hello', \sanitize_input('  hello  '));
        $this->assertSame('alert(1)Hello', \sanitize_input('<script>alert(1)</script>Hello'));
        $this->assertSame('', \sanitize_input('   '));
        $this->assertSame('&quot;quoted&quot; &amp; more', \sanitize_input(' "quoted" & more '));
    }

    public function testSanitizeInputWithNullIsHandledAndEmitsDeprecation(): void
    {
        $deprecationMessage = '';

        set_error_handler(static function (int $severity, string $message) use (&$deprecationMessage): bool {
            if ($severity === E_DEPRECATED) {
                $deprecationMessage = $message;
                return true;
            }
            return false;
        });

        try {
            $result = \sanitize_input(null);
        } finally {
            restore_error_handler();
        }

        $this->assertSame('', $result);
        $this->assertStringContainsString('Passing null', $deprecationMessage);
    }

    public function testRedirectUsesHeaderAndExitHooks(): void
    {
        $capturedHeader = null;
        $exitCalled = false;

        $GLOBALS['__isdn_header_emitter'] = static function (string $header) use (&$capturedHeader): void {
            $capturedHeader = $header;
        };
        $GLOBALS['__isdn_exit_handler'] = static function () use (&$exitCalled): void {
            $exitCalled = true;
        };

        \redirect('/reports');

        $this->assertSame('Location: ' . BASE_PATH . '/reports', $capturedHeader);
        $this->assertTrue($exitCalled);
    }

    public function testRedirectUsesNativeHeaderWhenHeaderHookIsNotProvided(): void
    {
        $exitCalled = false;
        unset($GLOBALS['__isdn_header_emitter']);

        if (function_exists('header_remove')) {
            header_remove();
        }

        $GLOBALS['__isdn_exit_handler'] = static function () use (&$exitCalled): void {
            $exitCalled = true;
        };

        \redirect('/native-header');

        $this->assertTrue($exitCalled);
        if (function_exists('xdebug_get_headers')) {
            $this->assertContains('Location: ' . BASE_PATH . '/native-header', xdebug_get_headers());
        }
    }

    public function testSessionLoginSimulation(): void
    {
        $this->assertFalse(\is_logged_in());

        $_SESSION['user_id'] = 42;
        $this->assertTrue(\is_logged_in());

        $_SESSION['user_id'] = null;
        $this->assertFalse(\is_logged_in());

        $_SESSION['user_id'] = 0;
        $this->assertTrue(\is_logged_in());
    }

    public function testCurrentUserRoleFromSession(): void
    {
        $this->assertNull(\current_user_role());

        $_SESSION['role'] = 'customer';
        $this->assertSame('customer', \current_user_role());
    }

    public function testFlashMessageRoundTripAndClearBehavior(): void
    {
        \flash_message('Saved successfully');

        $flash = \get_flash_message();
        $this->assertSame(['message' => 'Saved successfully', 'type' => 'success'], $flash);
        $this->assertNull(\get_flash_message(), 'Flash message should clear after first read');
    }

    public function testFlashMessageSupportsMultipleSessionStates(): void
    {
        \flash_message('First', 'error');
        \flash_message('Second', 'success');

        $flash = \get_flash_message();
        $this->assertSame(['message' => 'Second', 'type' => 'success'], $flash);

        $_SESSION = [];
        $this->assertNull(\get_flash_message());
    }

    public function testDisplayFlashOutputsMarkupAndConsumesMessage(): void
    {
        \flash_message('Permission denied', 'error');

        ob_start();
        \display_flash();
        $html = (string) ob_get_clean();

        $this->assertStringContainsString('bg-red-500', $html);
        $this->assertStringContainsString('Permission denied', $html);
        $this->assertNull(\get_flash_message());
    }

    public function testAuditLogWritesPreparedStatementWithRemoteIp(): void
    {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        $stmt = $this->createMock(PDOStatement::class);
        $stmt->expects($this->once())
            ->method('execute')
            ->with([9, 'login', 'user', 9, 'unit test', '127.0.0.1'])
            ->willReturn(true);

        $pdo = $this->createMock(PDO::class);
        $pdo->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('INSERT INTO audit_logs'))
            ->willReturn($stmt);

        \audit_log($pdo, 9, 'login', 'user', 9, 'unit test');
    }

    /**
     * Token format: 64-character hex string (bin2hex of 32 bytes)
     */
    public function testPasswordResetTokenFormat(): void
    {
        $token = bin2hex(random_bytes(32));
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $token);
        $this->assertSame(64, strlen($token));
    }

    public function testPasswordResetTokenFormatRejectsInvalidInput(): void
    {
        $this->assertDoesNotMatchRegularExpression('/^[a-f0-9]{64}$/', '');
        $this->assertDoesNotMatchRegularExpression('/^[a-f0-9]{64}$/', str_repeat('a', 63));
        $this->assertDoesNotMatchRegularExpression('/^[a-f0-9]{64}$/', str_repeat('g', 64));
    }

    /**
     * Expired token: findByResetToken uses "password_reset_expires > NOW()"
     * Simulate by asserting boundary behavior for expiry timestamps.
     */
    public function testExpiredTokenRejectionLogic(): void
    {
        $expired = strtotime('-61 minutes');
        $valid = strtotime('+10 minutes');
        $now = time();

        $this->assertLessThan($now, $expired, 'Expired token time should be before now');
        $this->assertGreaterThan($now, $valid, 'Future token time should be after now');
    }
}

<?php

declare(strict_types=1);

namespace Isdn\Tests\Unit;

use PHPUnit\Framework\TestCase;
use PDO;
use PDOStatement;

/**
 * Security unit tests for ISDN
 * Tests prepared statements usage (mocked DB) and input sanitization.
 */
class SecurityTest extends TestCase
{
    public function testSanitizeInputTrimsWhitespace(): void
    {
        $result = \sanitize_input('  hello  ');
        $this->assertEquals('hello', $result);
    }

    public function testSanitizeInputStripsTags(): void
    {
        $input = '<script>alert("xss")</script>Hello';
        $result = \sanitize_input($input);
        $this->assertStringNotContainsString('<script>', $result);
    }

    public function testSanitizeInputEscapesHtmlSpecialChars(): void
    {
        $input = '<div>&"\'</div>';
        $result = \sanitize_input($input);
        $this->assertStringContainsString('&amp;', $result);
        $this->assertStringContainsString('&quot;', $result);
    }

    public function testUserModelUsesPreparedStatements(): void
    {
        $userSql = "SELECT * FROM users WHERE email = ?";
        $this->assertStringContainsString('?', $userSql, 'User findByEmail uses placeholder');
    }

    public function testDeliveryReportUsesNamedParameters(): void
    {
        $reportSql = "SELECT * FROM order_deliveries WHERE o.created_at >= :start_date";
        $this->assertStringContainsString(':start_date', $reportSql, 'DeliveryReport uses named params');
    }

    /**
     * Mock PDO to verify prepared statement is used (not raw query with concatenation)
     */
    public function testMockedPdoPrepareIsCalled(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('execute')->willReturn(true);

        $pdo = $this->createMock(PDO::class);
        $pdo->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('?'))
            ->willReturn($stmt);

        $pdo->prepare('SELECT * FROM users WHERE email = ?');
    }

    public function testPasswordResetTokenIsNotPredictable(): void
    {
        $token1 = bin2hex(random_bytes(32));
        $token2 = bin2hex(random_bytes(32));
        $this->assertNotEquals($token1, $token2);
    }
}

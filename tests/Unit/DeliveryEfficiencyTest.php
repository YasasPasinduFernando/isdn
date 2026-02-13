<?php

declare(strict_types=1);

namespace Isdn\Tests\Unit;

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/includes/delivery_efficiency_helper.php';

/**
 * Delivery efficiency logic unit tests for ISDN
 * Tests efficiency calculation, zero-division safety, and performance classification.
 */
class DeliveryEfficiencyTest extends TestCase
{
    public function testCorrectEfficiencyPercentageCalculation(): void
    {
        $this->assertEquals(100.0, calculate_efficiency(10, 10));
        $this->assertEquals(50.0, calculate_efficiency(5, 10));
        $this->assertEquals(75.0, calculate_efficiency(15, 20));
        $this->assertEquals(80.0, calculate_efficiency(8, 10));
    }

    public function testZeroDivisionSafetyHandling(): void
    {
        $result = calculate_efficiency(5, 0);
        $this->assertNull($result, 'Zero completed deliveries should return null');
    }

    public function testZeroOnTimeDeliveriesReturnsZeroPercent(): void
    {
        $this->assertSame(0.0, calculate_efficiency(0, 8));
    }

    public function testEfficiencyCanExceedHundredWhenOnTimeExceedsCompleted(): void
    {
        $this->assertSame(120.0, calculate_efficiency(12, 10));
    }

    public function testPerformanceClassificationGood(): void
    {
        $this->assertEquals('good', classify_performance(80.0));
        $this->assertEquals('good', classify_performance(100.0));
        $this->assertEquals('good', classify_performance(90.5));
    }

    public function testPerformanceClassificationMedium(): void
    {
        $this->assertEquals('medium', classify_performance(50.0));
        $this->assertEquals('medium', classify_performance(79.9));
        $this->assertEquals('medium', classify_performance(65.0));
    }

    public function testPerformanceClassificationBad(): void
    {
        $this->assertEquals('bad', classify_performance(49.9));
        $this->assertEquals('bad', classify_performance(0.0));
        $this->assertEquals('bad', classify_performance(25.0));
    }

    public function testPerformanceClassificationEmptyWhenNull(): void
    {
        $this->assertEquals('empty', classify_performance(null));
    }

    public function testPerformanceClassificationEmptyWhenNegative(): void
    {
        $this->assertEquals('empty', classify_performance(-0.1));
    }

    public function testEfficiencyRoundsToOneDecimalPlace(): void
    {
        $result = calculate_efficiency(1, 3);
        $this->assertEquals(33.3, $result);
    }
}

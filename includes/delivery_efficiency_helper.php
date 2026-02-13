<?php
/**
 * Delivery Efficiency Helper - Testable calculation and classification logic
 * Used by delivery efficiency report views and unit tests.
 */

/**
 * Calculate efficiency percentage: (on_time / completed) * 100
 * Returns null when completed is 0 to avoid division by zero.
 */
function calculate_efficiency(int $onTime, int $completed): ?float
{
    if ($completed === 0) {
        return null;
    }
    return round(($onTime / $completed) * 100, 1);
}

/**
 * Classify performance based on efficiency percentage.
 * - good: >= 80%
 * - medium: 50% to 79%
 * - bad: < 50% or null (no completed deliveries)
 */
function classify_performance(?float $efficiency): string
{
    if ($efficiency === null || $efficiency < 0) {
        return 'empty';
    }
    if ($efficiency >= 80) {
        return 'good';
    }
    if ($efficiency >= 50) {
        return 'medium';
    }
    return 'bad';
}

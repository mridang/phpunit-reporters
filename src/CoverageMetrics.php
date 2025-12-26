<?php

declare(strict_types=1);

namespace Mridang\PHPUnitReporters;

/**
 * Represents coverage metrics with total and covered counts.
 *
 * This immutable value object holds the total number of coverable items
 * and how many were actually covered during test execution.
 */
final readonly class CoverageMetrics
{
    /**
     * Creates a new coverage metrics instance.
     *
     * @param int $total   The total number of coverable items.
     * @param int $covered The number of items that were covered.
     */
    public function __construct(
        public int $total,
        public int $covered,
    ) {
    }

    /**
     * Calculates the coverage percentage.
     *
     * @return float The percentage of covered items (0.0 to 100.0).
     *               Returns 0.0 if there are no items to cover.
     */
    public function getPercentage(): float
    {
        if ($this->total === 0) {
            return 0.0;
        }
        return ($this->covered / $this->total) * 100;
    }

    /**
     * Creates a zero-initialized metrics instance.
     *
     * @return self A metrics instance with total and covered both set to 0.
     */
    public static function zero(): self
    {
        return new self(0, 0);
    }

    /**
     * Adds another metrics instance to this one.
     *
     * @param CoverageMetrics $other The metrics to add.
     *
     * @return self A new instance with summed total and covered values.
     */
    public function add(CoverageMetrics $other): self
    {
        return new self(
            $this->total + $other->total,
            $this->covered + $other->covered,
        );
    }
}

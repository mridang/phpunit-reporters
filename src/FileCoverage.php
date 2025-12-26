<?php

declare(strict_types=1);

namespace Mridang\PHPUnitReporters;

/**
 * Represents coverage data for a single file.
 *
 * This immutable value object holds all coverage metrics for a source file
 * as parsed from a clover.xml report.
 */
final readonly class FileCoverage
{
    /**
     * Creates a new file coverage instance.
     *
     * @param CoverageMetrics $statements     Statement coverage metrics.
     * @param CoverageMetrics $branches       Branch/method coverage metrics.
     * @param CoverageMetrics $lines          Line coverage metrics.
     * @param list<int>       $uncoveredLines Line numbers without coverage.
     */
    public function __construct(
        public CoverageMetrics $statements,
        public CoverageMetrics $branches,
        public CoverageMetrics $lines,
        public array $uncoveredLines = [],
    ) {
    }
}

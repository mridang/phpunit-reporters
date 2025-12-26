<?php

declare(strict_types=1);

namespace Mridang\PHPUnitReporters\Tests;

use Mridang\PHPUnitReporters\CoverageMetrics;
use Mridang\PHPUnitReporters\FileCoverage;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Mridang\PHPUnitReporters\FileCoverage
 */
final class FileCoverageTest extends TestCase
{
    public function testConstructorSetsAllProperties(): void
    {
        $statements = new CoverageMetrics(10, 8);
        $branches = new CoverageMetrics(5, 3);
        $lines = new CoverageMetrics(15, 12);
        $uncoveredLines = [5, 10, 15];

        $coverage = new FileCoverage(
            $statements,
            $branches,
            $lines,
            $uncoveredLines
        );

        $this->assertSame($statements, $coverage->statements);
        $this->assertSame($branches, $coverage->branches);
        $this->assertSame($lines, $coverage->lines);
        $this->assertEquals([5, 10, 15], $coverage->uncoveredLines);
    }

    public function testConstructorDefaultsUncoveredLinesToEmptyArray(): void
    {
        $statements = new CoverageMetrics(10, 10);
        $branches = new CoverageMetrics(5, 5);
        $lines = new CoverageMetrics(15, 15);

        $coverage = new FileCoverage($statements, $branches, $lines);

        $this->assertEquals([], $coverage->uncoveredLines);
    }

    public function testIsImmutable(): void
    {
        $statements = new CoverageMetrics(10, 8);
        $branches = new CoverageMetrics(5, 3);
        $lines = new CoverageMetrics(15, 12);

        $coverage = new FileCoverage($statements, $branches, $lines);

        $this->assertEquals(10, $coverage->statements->total);
        $this->assertEquals(8, $coverage->statements->covered);
    }
}

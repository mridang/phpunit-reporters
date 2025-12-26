<?php

declare(strict_types=1);

namespace Mridang\PHPUnitReporters\Tests;

use Mridang\PHPUnitReporters\CoverageMetrics;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Mridang\PHPUnitReporters\CoverageMetrics
 */
final class CoverageMetricsTest extends TestCase
{
    public function testConstructorSetsProperties(): void
    {
        $metrics = new CoverageMetrics(100, 75);

        $this->assertEquals(100, $metrics->total);
        $this->assertEquals(75, $metrics->covered);
    }

    public function testGetPercentageCalculatesCorrectly(): void
    {
        $metrics = new CoverageMetrics(100, 75);

        $this->assertEquals(75.0, $metrics->getPercentage());
    }

    public function testGetPercentageReturnsZeroWhenTotalIsZero(): void
    {
        $metrics = new CoverageMetrics(0, 0);

        $this->assertEquals(0.0, $metrics->getPercentage());
    }

    public function testGetPercentageHandlesFullCoverage(): void
    {
        $metrics = new CoverageMetrics(50, 50);

        $this->assertEquals(100.0, $metrics->getPercentage());
    }

    public function testZeroCreatesZeroInitializedInstance(): void
    {
        $zero = CoverageMetrics::zero();

        $this->assertEquals(0, $zero->total);
        $this->assertEquals(0, $zero->covered);
    }

    public function testAddCombinesMetrics(): void
    {
        $metrics1 = new CoverageMetrics(10, 5);
        $metrics2 = new CoverageMetrics(20, 15);

        $result = $metrics1->add($metrics2);

        $this->assertEquals(30, $result->total);
        $this->assertEquals(20, $result->covered);
    }

    public function testAddDoesNotMutateOriginal(): void
    {
        $metrics1 = new CoverageMetrics(10, 5);
        $metrics2 = new CoverageMetrics(20, 15);

        $metrics1->add($metrics2);

        $this->assertEquals(10, $metrics1->total);
        $this->assertEquals(5, $metrics1->covered);
    }

    public function testAddWithZero(): void
    {
        $metrics = new CoverageMetrics(10, 5);
        $zero = CoverageMetrics::zero();

        $result = $metrics->add($zero);

        $this->assertEquals(10, $result->total);
        $this->assertEquals(5, $result->covered);
    }
}
